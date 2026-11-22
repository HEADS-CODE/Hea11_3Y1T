<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MemberRequest;
use App\Models\User;
use App\Notifications\PortalNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminRequestController extends Controller
{
    public function index(): View
    {
        $requests = MemberRequest::with(['member', 'submittedBy'])->latest()->paginate(12)->withQueryString();

        return view('admin.requests.index', compact('requests'));
    }

    public function show(MemberRequest $memberRequest): View
    {
        $memberRequest->load(['member.category', 'member.level', 'documents', 'submittedBy', 'reviewedBy']);

        return view('admin.requests.show', compact('memberRequest'));
    }

    public function approve(MemberRequest $memberRequest): RedirectResponse
    {
        if (! $memberRequest->isPending()) {
            return back()->with('error', 'This request has already been reviewed.');
        }
        if ($memberRequest->request_type === MemberRequest::TYPE_PROFILE_CHANGE
            && data_get($memberRequest->proposed_values, 'email') !== data_get($memberRequest->previous_values, 'email')
            && ! $memberRequest->email_reverified_at) {
            return back()->with('error', 'The proposed email address must be re-verified before approval.');
        }

        DB::transaction(function () use ($memberRequest): void {
            $member = $memberRequest->member;
            if ($memberRequest->request_type === MemberRequest::TYPE_REACTIVATION) {
                $member->update(['status' => 'active']);
                $member->user?->forceFill(['status' => User::STATUS_ACTIVE])->save();
            } elseif ($memberRequest->request_type === MemberRequest::TYPE_CATEGORY_CHANGE) {
                $member->update(['membership_category_id' => $memberRequest->proposed_values['membership_category_id']]);
            } elseif ($memberRequest->request_type === MemberRequest::TYPE_PROFILE_CHANGE) {
                $values = $memberRequest->proposed_values;
                $emailChanged = strtolower((string) ($values['email'] ?? '')) !== strtolower((string) $member->email);
                $member->update($values);
                if ($member->user) {
                    $member->user->forceFill([
                        'name' => $member->fresh()->full_name,
                        'email' => $member->email,
                        'email_verified_at' => $emailChanged ? now() : $member->user->email_verified_at,
                    ])->save();
                }
            }

            $memberRequest->update([
                'status' => MemberRequest::STATUS_APPROVED,
                'reviewed_by_user_id' => auth()->id(),
                'reviewed_at' => now(),
                'reviewer_notes' => request('reviewer_notes'),
            ]);
            AuditLog::record('member.request.approved', ['request_id' => $memberRequest->id, 'request_type' => $memberRequest->request_type], $memberRequest, auth()->user());
        });

        $memberRequest->member->user?->notify(new PortalNotification('Request approved', 'Your '.$this->label($memberRequest->request_type).' request was approved.', route('member.transactions')));

        return back()->with('success', 'Member request approved.');
    }

    public function reject(Request $request, MemberRequest $memberRequest): RedirectResponse
    {
        if (! $memberRequest->isPending()) {
            return back()->with('error', 'This request has already been reviewed.');
        }
        $validated = $request->validate(['reviewer_notes' => ['required', 'string', 'max:1000']]);
        $memberRequest->update([
            'status' => MemberRequest::STATUS_REJECTED,
            'reviewed_by_user_id' => auth()->id(),
            'reviewed_at' => now(),
            'reviewer_notes' => $validated['reviewer_notes'],
        ]);
        AuditLog::record('member.request.rejected', ['request_id' => $memberRequest->id, 'request_type' => $memberRequest->request_type], $memberRequest, auth()->user());
        $memberRequest->member->user?->notify(new PortalNotification('Request needs attention', 'Your '.$this->label($memberRequest->request_type).' request was not approved. Review the administrator note.', route('member.transactions')));

        return back()->with('success', 'Member request rejected with a note.');
    }

    public function downloadDocument(MemberRequest $memberRequest, int $document): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $memberDocument = $memberRequest->documents()->whereKey($document)->firstOrFail();
        Gate::authorize('view', $memberDocument);
        abort_unless(Storage::disk('member_private')->exists($memberDocument->storage_path), 404);

        return Storage::disk('member_private')->download($memberDocument->storage_path, $memberDocument->original_name, [
            'Content-Type' => $memberDocument->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function label(string $type): string
    {
        return config('membership.request_types.'.$type, str_replace('_', ' ', $type));
    }
}
