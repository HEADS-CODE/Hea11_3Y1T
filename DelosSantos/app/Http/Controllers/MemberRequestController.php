<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MemberRequest;
use App\Models\MembershipCategory;
use App\Models\User;
use App\Http\Requests\StoreCategoryChangeRequest;
use App\Http\Requests\StoreProfileChangeRequest;
use App\Http\Requests\StoreReactivationRequest;
use App\Notifications\EmailChangeVerificationNotification;
use App\Notifications\PortalNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MemberRequestController extends Controller
{
    public function index(): View
    {
        $member = auth()->user()->member;
        $requests = $member?->requests()->with('documents')->latest()->get() ?? collect();
        $extensionRequests = $member?->tasks()
            ->with(['extensionRequests' => fn ($query) => $query->latest()])
            ->get()
            ->flatMap(fn ($task) => $task->extensionRequests->map(fn ($extension) => [
                'kind' => 'task_extension',
                'label' => 'Task extension',
                'subject' => $task->task_title,
                'source' => 'online',
                'status' => $extension->status,
                'submitted_at' => $extension->created_at,
                'note' => $extension->decision_note,
                'documents' => collect(),
            ]))
            ->sortByDesc('submitted_at')
            ->values() ?? collect();

        return view('member-requests.index', compact('requests', 'extensionRequests'));
    }

    public function categoryChange(Request $request): View
    {
        $member = auth()->user()->member;
        abort_unless($member, 404);

        $selectedCategoryId = $request->integer('category') ?: (int) old('membership_category_id');

        return view('member-requests.category-change', [
            'member' => $member->load('category'),
            'categories' => MembershipCategory::where('is_active', true)->whereKeyNot($member->membership_category_id)->orderBy('name')->get(),
            'selectedCategoryId' => $selectedCategoryId,
        ]);
    }

    public function storeCategoryChange(StoreCategoryChangeRequest $request): RedirectResponse
    {
        $member = auth()->user()->member;
        abort_unless($member, 404);
        $validated = $request->validated();

        $memberRequest = $this->createRequest($member, MemberRequest::TYPE_CATEGORY_CHANGE, [
            'membership_category_id' => (int) $validated['membership_category_id'],
        ], ['membership_category_id' => $member->membership_category_id], MemberRequest::SOURCE_ONLINE);
        $this->storeDocuments($request, $memberRequest, 'category_eligibility');
        $this->notifyAdmins('New category-change request', $member->full_name.' submitted a category-change request.', route('admin.requests.show', $memberRequest));

        return redirect()->route('member.transactions')->with('success', 'Your category-change request was sent for review.');
    }

    public function profileChange(): View
    {
        $member = auth()->user()->member;
        abort_unless($member, 404);

        return view('member-requests.profile-change', compact('member'));
    }

    public function storeProfileChange(StoreProfileChangeRequest $request): RedirectResponse
    {
        $member = auth()->user()->member;
        abort_unless($member, 404);
        $validated = $request->validated();

        $proposed = collect($validated)->except(['privacy_consent', 'documents'])->map(fn ($value) => $value === '' ? null : $value)->all();
        $previous = collect(array_keys($proposed))->mapWithKeys(fn ($field) => [$field => $member->{$field}])->all();
        $identityChanged = collect(['first_name', 'middle_name', 'last_name', 'birth_date'])->contains(fn ($field) => (string) ($previous[$field] ?? '') !== (string) ($proposed[$field] ?? ''));
        $hasDocuments = count($request->file('documents', [])) > 0;

        $emailChanged = strtolower((string) $member->email) !== strtolower((string) $proposed['email']);
        $memberRequest = $this->createRequest($member, MemberRequest::TYPE_PROFILE_CHANGE, $proposed, $previous, MemberRequest::SOURCE_ONLINE);
        if ($hasDocuments) {
            $this->storeDocuments($request, $memberRequest, $identityChanged ? 'identity_or_birth' : 'profile_support');
        }

        if ($emailChanged) {
            $token = Str::random(64);
            $memberRequest->forceFill([
                'email_reverification_hash' => hash('sha256', $token),
                'email_reverification_expires_at' => now()->addHours(24),
            ])->save();
            Notification::route('mail', $proposed['email'])->notify(new EmailChangeVerificationNotification($memberRequest, $token));
        }

        $this->notifyAdmins('New profile-change request', $member->full_name.' submitted a profile-change request.', route('admin.requests.show', $memberRequest));

        return redirect()->route('member.transactions')->with('success', $emailChanged ? 'Your request was sent. Verify the new email address before administrator review.' : 'Your profile-change request was sent for review.');
    }

    public function storeReactivation(StoreReactivationRequest $request): RedirectResponse
    {
        $member = auth()->user()->member;
        abort_unless($member && $member->status === 'inactive' && $member->archived_at === null, 403);
        if ($member->requests()->where('request_type', MemberRequest::TYPE_REACTIVATION)->where('status', MemberRequest::STATUS_PENDING)->exists()) {
            return back()->with('info', 'Your reactivation request is already pending review.');
        }

        $validated = $request->validated();
        $memberRequest = $this->createRequest($member, MemberRequest::TYPE_REACTIVATION, [], ['status' => $member->status], MemberRequest::SOURCE_ONLINE);
        $this->storeDocuments($request, $memberRequest, 'reactivation_support');
        $this->notifyAdmins('Reactivation request received', $member->full_name.' submitted a reactivation request.', route('admin.requests.show', $memberRequest));

        return back()->with('success', 'Your reactivation request was sent for administrator review.');
    }

    public function verifyEmailChange(MemberRequest $memberRequest, string $token): RedirectResponse
    {
        abort_unless($memberRequest->request_type === MemberRequest::TYPE_PROFILE_CHANGE, 404);
        abort_unless($memberRequest->email_reverification_expires_at?->isFuture(), 403);
        abort_unless(hash_equals((string) $memberRequest->email_reverification_hash, hash('sha256', $token)), 403);

        $memberRequest->forceFill([
            'email_reverified_at' => now(),
            'email_reverification_hash' => null,
        ])->save();

        return redirect()->route('login.member')->with('success', 'The new email address was verified. An administrator can now review the profile request.');
    }

    private function createRequest($member, string $type, array $proposed, array $previous, string $source): MemberRequest
    {
        return $member->requests()->create([
            'submitted_by_user_id' => auth()->id(),
            'request_type' => $type,
            'source' => $source,
            'status' => MemberRequest::STATUS_PENDING,
            'previous_values' => $previous,
            'proposed_values' => $proposed,
            'consent_at' => now(),
        ]);
    }

    private function storeDocuments(Request $request, MemberRequest $memberRequest, string $documentType): void
    {
        foreach ($request->file('documents', []) as $file) {
            $extension = strtolower($file->extension());
            $name = Str::random(40).'.'.$extension;
            $path = $file->storeAs('', $name, 'member_private');
            $memberRequest->documents()->create([
                'member_id' => $memberRequest->member_id,
                'uploaded_by_user_id' => auth()->id(),
                'document_type' => $documentType,
                'original_name' => $file->getClientOriginalName(),
                'storage_path' => $path,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'extension' => $extension,
                'size_bytes' => $file->getSize(),
                'privacy_consented_at' => now(),
            ]);
        }
    }

    private function notifyAdmins(string $title, string $message, string $url): void
    {
        User::where('role', User::ROLE_ADMIN)->where('status', User::STATUS_ACTIVE)->get()->each->notify(new PortalNotification($title, $message, $url));
    }
}
