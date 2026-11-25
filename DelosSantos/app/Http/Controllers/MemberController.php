<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\EmailVerificationToken;
use App\Models\Member;
use App\Models\MemberDocument;
use App\Models\MemberInvitation;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\User;
use App\Notifications\MemberInvitationNotification;
use App\Notifications\VerifyEmailNotification;
use App\Rules\PhilippinePhone;
use App\Rules\MinimumAge;
use App\Services\MembershipTransactionService;
use App\Services\MemberIdentityDeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $query = Member::with(['category', 'level']);
        $request->boolean('show_archived') ? $query->whereNotNull('archived_at') : $query->whereNull('archived_at');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($memberQuery) use ($search) {
                $memberQuery->where('membership_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $query->when($request->filled('category_id'), fn ($q) => $q->where('membership_category_id', $request->integer('category_id')))
            ->when($request->filled('level_id'), fn ($q) => $q->where('membership_level_id', $request->integer('level_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()));

        $members = $query->paginate(10)->withQueryString();
        $categories = MembershipCategory::orderBy('name')->get();
        $levels = MembershipLevel::orderBy('fee')->get();
        $stats = [
            'total' => Member::whereNull('archived_at')->count(),
            'active' => Member::where('status', 'active')->whereNull('archived_at')->whereHas('transactions', fn ($query) => $query->where('status', 'approved')->whereDate('ends_at', '>=', today()))->count(),
            'inactive' => Member::whereNull('archived_at')->where('status', 'inactive')->count(),
            'categories' => MembershipCategory::count(),
        ];

        return view('members.index-v2', compact('members', 'categories', 'levels', 'stats'));
    }

    public function create(): View
    {
        return view('members.create', [
            'categories' => MembershipCategory::where('is_active', true)->orderBy('name')->get(),
            'levels' => MembershipLevel::where('is_active', true)->orderBy('fee')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['membership_number'] = $this->generateMembershipNumber();
        $invitation = null;

        DB::transaction(function () use ($validated, &$invitation): void {
            $member = Member::create([
                ...$validated,
                'status' => 'pending',
            ]);

            $user = new User();
            $user->forceFill([
                'member_id' => $member->id,
                'name' => $member->full_name,
                'email' => $member->email,
                'password' => Hash::make(Str::random(64)),
                'role' => User::ROLE_MEMBER,
                'status' => User::STATUS_PENDING,
            ])->save();

            app(MembershipTransactionService::class)->create([
                'member' => $member,
                'category' => $member->category,
                'level' => $member->level,
                'type' => \App\Models\MembershipTransaction::TYPE_APPLICATION,
                'created_by_user_id' => auth()->id(),
            ]);

            [$invitation, $rawToken] = MemberInvitation::issueFor($member, $user, auth()->user());
            DB::afterCommit(fn () => $user->notify(new MemberInvitationNotification(
                route('member.invitation.show', ['token' => $rawToken]),
            )));

            AuditLog::record('member.created', [
                'member_id' => $member->id,
                'status' => 'pending',
                'invitation_sent' => true,
            ], $member, auth()->user());
        });

        return redirect()->route('members.index')->with('success', 'Member created as pending. A secure invitation was sent so they can set a password and confirm their email.');
    }

    public function show(Member $member): View
    {
        $member->load(['category', 'level']);

        return view('members.show-v2', compact('member'));
    }

    public function documents(Member $member): View
    {
        $member->load(['documents' => fn ($query) => $query->with('request')->latest()]);

        return view('members.documents', compact('member'));
    }

    public function documentViewer(Member $member, MemberDocument $document): Response
    {
        abort_unless((int) $document->member_id === (int) $member->id, 404);
        Gate::authorize('view', $document);

        return response()->view('members.document-viewer', compact('member', 'document'))
            ->header('Cache-Control', 'no-store, private, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function viewDocument(Member $member, MemberDocument $document): BinaryFileResponse
    {
        abort_unless((int) $document->member_id === (int) $member->id, 404);
        Gate::authorize('view', $document);

        $disk = Storage::disk('member_private');
        abort_unless($disk->exists($document->storage_path), 404);

        return response()->file($disk->path($document->storage_path), [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $document->original_name).'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, private, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function edit(Member $member): View
    {
        return view('members.edit', [
            'member' => $member,
            'categories' => MembershipCategory::orderBy('name')->get(),
            'levels' => MembershipLevel::orderBy('fee')->get(),
        ]);
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $validated = $this->validated($request, $member);
        $validated['email'] = strtolower($validated['email']);
        $oldEmail = strtolower((string) $member->email);
        $verification = null;

        DB::transaction(function () use ($validated, $member, $oldEmail, &$verification): void {
            $member->update($validated);

            if (! $member->user) {
                AuditLog::record('member.profile.updated', ['member_id' => $member->id, 'account_linked' => false], $member, auth()->user());
                return;
            }

            $emailChanged = $oldEmail !== strtolower((string) $member->email);
            $user = $member->user;
            $user->forceFill([
                'name' => $member->full_name,
                'email' => $member->email,
            ]);

            if ($emailChanged) {
                $user->forceFill([
                    'email_verified_at' => null,
                    'status' => User::STATUS_PENDING,
                ]);
                $member->forceFill(['status' => 'pending'])->save();
            }

            $user->save();

            AuditLog::record('member.profile.updated', ['member_id' => $member->id, 'email_changed' => $emailChanged], $member, auth()->user());

            if ($emailChanged) {
                $token = EmailVerificationToken::issueFor($user);
                $verification = [$user, $token];
            }
        });

        if ($verification) {
            [$user, $token] = $verification;
            $user->notify(new VerifyEmailNotification($token));
        }

        return redirect()->route('members.show', $member)->with('success', 'Member details updated.');
    }

    public function destroy(Member $member, MemberIdentityDeletionService $identityDeletion): RedirectResponse
    {
        if (trim((string) request()->input('confirm_name')) !== $member->full_name) {
            return back()->withErrors(['confirm_name' => 'Type the member’s full name exactly to confirm permanent deletion.']);
        }

        DB::transaction(function () use ($member, $identityDeletion): void {
            $identityDeletion->delete($member, auth()->user());
        });

        return redirect()->route('members.index')->with('success', 'Member profile and login were permanently deleted. Financial transaction history and payments were preserved for administrators.');
    }

    public function archive(Member $member): RedirectResponse
    {
        DB::transaction(function () use ($member): void {
            $member->update(['status' => 'inactive', 'archived_at' => now()]);
            $member->user?->forceFill(['status' => User::STATUS_DISABLED])->save();
            AuditLog::record('member.archived', ['member_id' => $member->id], $member, auth()->user());
        });

        return redirect()->route('members.index')->with('success', 'Member archived and hidden from the default directory.');
    }

    public function restore(Member $member): RedirectResponse
    {
        DB::transaction(function () use ($member): void {
            $member->update(['archived_at' => null, 'status' => 'inactive']);
            $member->user?->forceFill(['status' => User::STATUS_DISABLED])->save();
            AuditLog::record('member.restored', ['member_id' => $member->id], $member, auth()->user());
        });

        return redirect()->route('members.index', ['show_archived' => 1])->with('success', 'Member restored from the archive as inactive.');
    }

    public function toggleStatus(Member $member): RedirectResponse
    {
        $member->update(['status' => $member->status === 'active' ? 'inactive' : 'active']);
        $message = $member->status === 'active' ? 'Member reactivated.' : 'Member deactivated.';

        return back()->with('success', $message);
    }

    private function validated(Request $request, ?Member $member = null): array
    {
        $categoryName = MembershipCategory::whereKey($request->input('membership_category_id'))->value('name');
        $isSeniorCitizen = is_string($categoryName) && strcasecmp(trim($categoryName), 'Senior Citizen') === 0;
        $birthDateRequired = $member === null || $isSeniorCitizen;

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('members', 'email')->ignore($member?->id),
                Rule::unique('users', 'email')->where(fn ($query) => $query->where('role', User::ROLE_MEMBER))->ignore($member?->user?->id),
            ],
            'phone' => ['nullable', 'string', 'max:30', new PhilippinePhone()],
            'address' => ['nullable', 'string', 'max:500'],
            'unit_floor_building' => ['nullable', 'string', 'max:150'],
            'house_street' => ['nullable', 'string', 'max:150'],
            'barangay' => ['nullable', 'string', 'max:100'],
            'city_municipality' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'zip_code' => ['nullable', 'digits:4'],
            'birth_date' => [
                $birthDateRequired ? 'required' : 'nullable',
                'date',
                'before_or_equal:today',
                new MinimumAge(
                    $isSeniorCitizen ? 60 : 18,
                    $isSeniorCitizen
                        ? 'You must be at least 60 years old for the Senior Citizen category.'
                        : 'Members must be at least 18 years old.',
                ),
            ],
            'gender' => ['required', Rule::in(config('membership.genders'))],
            'membership_category_id' => ['required', 'exists:membership_categories,id'],
            'membership_level_id' => ['required', 'exists:membership_levels,id'],
            'status' => ['required', Rule::in(['pending', 'active', 'inactive'])],
            'joined_at' => ['required', 'date'],
        ]);

        $validated['email'] = strtolower(trim($validated['email']));

        return $validated;
    }

    private function generateMembershipNumber(): string
    {
        do {
            $number = '17' . str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        } while (Member::where('membership_number', $number)->exists());

        return $number;
    }
}
