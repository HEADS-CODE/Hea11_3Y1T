<?php

use App\Models\EmailVerificationToken;
use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function makePendingMemberForAuthTests(string $email = 'member@example.test'): Member
{
    $category = MembershipCategory::create(['name' => 'Student', 'is_active' => true]);
    $level = MembershipLevel::create(['name' => 'Basic', 'fee' => 399, 'validity_months' => 1, 'is_active' => true]);

    return Member::create([
        'membership_number' => '1700001',
        'first_name' => 'Pending',
        'last_name' => 'Member',
        'email' => $email,
        'membership_category_id' => $category->id,
        'membership_level_id' => $level->id,
        'status' => 'pending',
        'joined_at' => now()->toDateString(),
    ]);
}

test('unverified accounts reach verification and verified pending accounts reach approval', function () {
    $member = makePendingMemberForAuthTests();
    $user = User::factory()->member($member)->create(['email' => $member->email]);
    $user->forceFill(['status' => User::STATUS_PENDING, 'email_verified_at' => null])->save();

    $response = $this->post(route('login.member.submit'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('verification.notice'));
    $this->assertAuthenticatedAs($user);

    $user->forceFill(['email_verified_at' => now()])->save();
    Auth::logout();

    $response = $this->post(route('login.member.submit'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('approval.pending'));
    $this->assertAuthenticatedAs($user);
});

test('member and administrator credentials cannot cross roles for the same email', function () {
    $member = makePendingMemberForAuthTests('dual-role@example.test');
    $member->forceFill(['status' => 'active'])->save();
    $memberUser = User::factory()->member($member)->create(['email' => $member->email]);
    $memberUser->forceFill(['password' => Hash::make('member-secret'), 'email_verified_at' => now()])->save();

    $admin = User::factory()->admin()->create(['email' => $member->email]);
    $admin->forceFill(['password' => Hash::make('admin-secret'), 'email_verified_at' => now()])->save();

    $response = $this->post(route('login.member.submit'), [
        'email' => $member->email,
        'password' => 'admin-secret',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();

    $response = $this->post(route('login.admin.submit'), [
        'email' => $admin->email,
        'password' => 'admin-secret',
    ]);

    $response->assertRedirect(route('members.index'));
    $this->assertAuthenticatedAs($admin);
});

test('email verification consumes a valid token and records the verified state', function () {
    $member = makePendingMemberForAuthTests('verify@example.test');
    $user = User::factory()->member($member)->create(['email' => $member->email]);
    $user->forceFill(['status' => User::STATUS_PENDING, 'email_verified_at' => null])->save();
    $token = EmailVerificationToken::issueFor($user);

    $response = $this->get(route('verification.verify', ['user' => $user, 'token' => $token]));

    $response->assertRedirect(route('login.member'));
    expect($user->fresh()->email_verified_at)->not->toBeNull();
    expect(EmailVerificationToken::first()->fresh()->consumed_at)->not->toBeNull();
});

test('member and administrator password resets remain isolated for the same email', function () {
    Notification::fake();
    $member = makePendingMemberForAuthTests('reset-dual@example.test');
    $member->forceFill(['status' => 'active'])->save();
    $memberUser = User::factory()->member($member)->create(['email' => $member->email]);
    $memberUser->forceFill(['email_verified_at' => now()])->save();
    $admin = User::factory()->admin()->create(['email' => $member->email]);
    $admin->forceFill(['email_verified_at' => now()])->save();

    $memberToken = Password::broker('members')->createToken($memberUser);

    $wrongRole = Password::broker('admins')->reset([
        'email' => $admin->email,
        'password' => 'AdminNew123',
        'password_confirmation' => 'AdminNew123',
        'token' => $memberToken,
        'role' => User::ROLE_ADMIN,
    ], fn (User $user, string $password) => $user->forceFill(['password' => Hash::make($password)])->save());

    expect($wrongRole)->not->toBe(Password::PASSWORD_RESET);

    $memberReset = Password::broker('members')->reset([
        'email' => $memberUser->email,
        'password' => 'MemberNew123',
        'password_confirmation' => 'MemberNew123',
        'token' => $memberToken,
        'role' => User::ROLE_MEMBER,
    ], fn (User $user, string $password) => $user->forceFill(['password' => Hash::make($password)])->save());

    expect($memberReset)->toBe(Password::PASSWORD_RESET)
        ->and(Hash::check('MemberNew123', $memberUser->fresh()->password))->toBeTrue();
});

test('member registration remains usable when the maps key is missing', function () {
    config(['services.google.maps_key' => null]);
    $response = $this->get(route('register.member'));

    $response->assertOk()->assertSee('Map pin is unavailable right now. You can continue using the typed address.');
});

test('admin member edits keep the account profile synchronized and reverify a changed email', function () {
    Notification::fake();
    $member = makePendingMemberForAuthTests('sync-old@example.test');
    $member->forceFill(['status' => 'active'])->save();
    $user = User::factory()->member($member)->create(['email' => $member->email]);
    $user->forceFill(['email_verified_at' => now()])->save();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->put(route('members.update', $member), [
        'first_name' => 'Synced',
        'last_name' => 'Profile',
        'email' => 'sync-new@example.test',
        'gender' => 'Prefer not to say',
        'membership_category_id' => $member->membership_category_id,
        'membership_level_id' => $member->membership_level_id,
        'status' => 'active',
        'joined_at' => $member->joined_at->toDateString(),
    ])->assertRedirect(route('members.show', $member));

    expect($user->fresh()->name)->toBe('Synced Profile')
        ->and($user->fresh()->email)->toBe('sync-new@example.test')
        ->and($user->fresh()->email_verified_at)->toBeNull()
        ->and($user->fresh()->status)->toBe(User::STATUS_PENDING)
        ->and($member->fresh()->status)->toBe('pending');
});
