<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationToken;
use App\Models\AdminProfile;
use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\PortalNotification;
use App\Services\MembershipTransactionService;
use App\Rules\PhilippinePhone;
use App\Rules\MinimumAge;
use App\Rules\ProjectPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function showMemberRegistration(): View
    {
        return view('auth.register-member-v2', [
            'categories' => MembershipCategory::where('is_active', true)->orderBy('name')->get(),
            'levels' => MembershipLevel::where('is_active', true)->orderBy('fee')->get(),
        ]);
    }

    public function showAdminRegistration(): View
    {
        return view('auth.register-admin-complete');
    }

    public function registerMember(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('members', 'email'), Rule::unique('users', 'email')->where(fn ($query) => $query->where('role', User::ROLE_MEMBER))],
            'phone' => ['nullable', 'string', 'max:30', new PhilippinePhone()],
            'birth_date' => ['required', 'date', 'before_or_equal:today', $this->minimumAgeRule($request->input('membership_category_id'))],
            'gender' => ['required', Rule::in(config('membership.genders'))],
            'address' => ['nullable', 'string', 'max:500'],
            'unit_floor_building' => ['nullable', 'string', 'max:150'],
            'house_street' => ['required', 'string', 'max:150'],
            'barangay' => ['required', 'string', 'max:100'],
            'city_municipality' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'zip_code' => ['required', 'digits:4'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'membership_category_id' => ['required', Rule::exists('membership_categories', 'id')->where(fn ($query) => $query->where('is_active', true))],
            'membership_level_id' => ['required', Rule::exists('membership_levels', 'id')->where(fn ($query) => $query->where('is_active', true))],
            'accepted' => ['accepted'],
            'password' => ProjectPassword::rules(),
        ]);

        DB::transaction(function () use ($validated): void {
            $member = Member::create([
                'membership_number' => $this->generateMembershipNumber(),
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'gender' => $validated['gender'],
                'email' => strtolower($validated['email']),
                'phone' => $validated['phone'] ?? null,
                'birth_date' => $validated['birth_date'],
                'address' => $validated['address'] ?? null,
                'unit_floor_building' => $validated['unit_floor_building'] ?? null,
                'house_street' => $validated['house_street'],
                'barangay' => $validated['barangay'],
                'city_municipality' => $validated['city_municipality'],
                'province' => $validated['province'],
                'zip_code' => $validated['zip_code'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'membership_category_id' => $validated['membership_category_id'],
                'membership_level_id' => $validated['membership_level_id'],
                'status' => 'pending',
                'joined_at' => now()->toDateString(),
            ]);

            $user = new User();
            $user->forceFill([
                'member_id' => $member->id,
                'name' => $member->full_name,
                'email' => $member->email,
                'password' => Hash::make($validated['password']),
                'role' => User::ROLE_MEMBER,
                'status' => User::STATUS_PENDING,
            ])->save();

            app(MembershipTransactionService::class)->create([
                'member' => $member,
                'category' => $member->category,
                'level' => $member->level,
                'type' => \App\Models\MembershipTransaction::TYPE_APPLICATION,
            ]);

            $token = EmailVerificationToken::issueFor($user);
            DB::afterCommit(fn () => $user->notify(new VerifyEmailNotification($token)));
            DB::afterCommit(fn () => $user->notify(new PortalNotification('Welcome to Puihaha', 'Your member registration was received. Confirm your email, then sign in using your new member account to complete the membership payment when prompted.', route('approval.pending'))));
            DB::afterCommit(fn () => User::where('role', User::ROLE_ADMIN)->where('status', User::STATUS_ACTIVE)->get()->each(fn (User $admin) => $admin->notify(new PortalNotification('New member registration', $member->full_name.' submitted a membership application.', route('admin.approvals.index')))));
        });

        return redirect()->route('login.member')->with('success', 'Registration received. Check your email to confirm your account.');
    }

    public function registerAdmin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->where(fn ($query) => $query->where('role', User::ROLE_ADMIN))],
            'phone' => ['required', 'string', 'max:30', new PhilippinePhone()],
            'birth_date' => ['required', 'date', 'before_or_equal:today', new MinimumAge(18, 'You must be at least 18 years old to register as an administrator.')],
            'gender' => ['required', Rule::in(config('membership.genders'))],
            'address' => ['nullable', 'string', 'max:500'],
            'unit_floor_building' => ['nullable', 'string', 'max:150'],
            'house_street' => ['required', 'string', 'max:150'],
            'barangay' => ['required', 'string', 'max:100'],
            'city_municipality' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'zip_code' => ['required', 'digits:4'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'accepted' => ['accepted'],
            'password' => ProjectPassword::rules(),
        ]);

        DB::transaction(function () use ($validated): void {
            $user = new User();
            $user->forceFill([
                'name' => trim(implode(' ', array_filter([$validated['first_name'], $validated['middle_name'] ?? null, $validated['last_name']]))),
                'email' => strtolower($validated['email']),
                'password' => Hash::make($validated['password']),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_PENDING,
            ])->save();

            AdminProfile::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'birth_date' => $validated['birth_date'],
                'gender' => $validated['gender'],
                'address' => $validated['address'] ?? null,
                'unit_floor_building' => $validated['unit_floor_building'] ?? null,
                'house_street' => $validated['house_street'],
                'barangay' => $validated['barangay'],
                'city_municipality' => $validated['city_municipality'],
                'province' => $validated['province'],
                'zip_code' => $validated['zip_code'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
            ]);

            $token = EmailVerificationToken::issueFor($user);
            DB::afterCommit(fn () => $user->notify(new VerifyEmailNotification($token)));
            DB::afterCommit(fn () => User::where('role', User::ROLE_ADMIN)->where('status', User::STATUS_ACTIVE)->get()->each(fn (User $admin) => $admin->notify(new PortalNotification('New administrator registration', $user->name.' requested administrator access.', route('admin.approvals.index')))));
        });

        return redirect()->route('login.admin')->with('success', 'Administrator registration received. Confirm your email, then wait for approval.');
    }

    private function generateMembershipNumber(): string
    {
        do {
            $number = '17' . str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        } while (Member::where('membership_number', $number)->exists());

        return $number;
    }

    private function minimumAgeRule(mixed $categoryId): MinimumAge
    {
        $categoryName = $categoryId ? MembershipCategory::whereKey($categoryId)->value('name') : null;
        $isSeniorCitizen = is_string($categoryName) && strcasecmp(trim($categoryName), 'Senior Citizen') === 0;

        return new MinimumAge(
            $isSeniorCitizen ? 60 : 18,
            $isSeniorCitizen
                ? 'You must be at least 60 years old for the Senior Citizen category.'
                : 'You must be at least 18 years old to register.',
        );
    }
}
