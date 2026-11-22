<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\EmailVerificationToken;
use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Rules\PhilippinePhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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

        Member::create($validated);

        return redirect()->route('members.index')->with('success', 'Member registered successfully.');
    }

    public function show(Member $member): View
    {
        $member->load(['category', 'level']);

        return view('members.show-v2', compact('member'));
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

    public function destroy(Member $member): RedirectResponse
    {
        if ($member->transactions()->exists()) {
            DB::transaction(function () use ($member): void {
                $member->update(['status' => 'inactive', 'archived_at' => now()]);
                $member->user?->forceFill(['status' => User::STATUS_DISABLED])->save();
                AuditLog::record('member.archived', ['member_id' => $member->id, 'reason' => 'delete_requested_with_transaction_history'], $member, auth()->user());
            });

            return redirect()->route('members.index')->with('success', 'This member has transaction history, so the delete request archived the member instead of removing the record.');
        }

        if (trim((string) request()->input('confirm_name')) !== $member->full_name) {
            return back()->withErrors(['confirm_name' => 'Type the member’s full name exactly to confirm permanent deletion.']);
        }

        DB::transaction(function () use ($member): void {
            AuditLog::record('member.deleted', ['member_id' => $member->id, 'membership_number' => $member->membership_number], $member, auth()->user());
            $member->tasks()->delete();
            $member->transactions()->whereIn('status', ['pending', 'cancelled'])->delete();
            $member->user?->delete();
            $member->delete();
        });

        return redirect()->route('members.index')->with('success', 'Member and its non-financial records were permanently deleted.');
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
        return $request->validate([
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
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['required', Rule::in(config('membership.genders'))],
            'membership_category_id' => ['required', 'exists:membership_categories,id'],
            'membership_level_id' => ['required', 'exists:membership_levels,id'],
            'status' => ['required', Rule::in(['pending', 'active', 'inactive'])],
            'joined_at' => ['required', 'date'],
        ]);
    }

    private function generateMembershipNumber(): string
    {
        do {
            $number = '17' . str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        } while (Member::where('membership_number', $number)->exists());

        return $number;
    }
}
