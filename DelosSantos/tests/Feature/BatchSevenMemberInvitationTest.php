<?php

use App\Models\AuditLog;
use App\Models\Member;
use App\Models\MemberInvitation;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\User;
use App\Notifications\MemberInvitationNotification;
use App\Notifications\RoleScopedResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function batchSevenBAdminMemberPayload(string $email, int $categoryId, int $levelId): array
{
    return [
        'first_name' => 'Invited',
        'last_name' => 'Member',
        'email' => $email,
        'phone' => '',
        'birth_date' => '1990-01-01',
        'gender' => config('membership.genders')[0],
        'address' => 'Typed address',
        'house_street' => '1 Test Street',
        'barangay' => 'Test Barangay',
        'city_municipality' => 'Test City',
        'province' => 'Test Province',
        'zip_code' => '1000',
        'membership_category_id' => $categoryId,
        'membership_level_id' => $levelId,
        'status' => 'active',
        'joined_at' => today()->toDateString(),
    ];
}

function createBatchSevenBInvitedMember($test, string $email = 'invited-member@example.test'): array
{
    $suffix = substr(md5($email), 0, 8);
    $category = MembershipCategory::create(['name' => 'Invitation Category '.$suffix, 'is_active' => true]);
    $level = MembershipLevel::create(['name' => 'Invitation Level '.$suffix, 'fee' => 500, 'validity_months' => 12, 'is_active' => true]);
    $admin = User::factory()->admin()->create();

    Notification::fake();

    $test->actingAs($admin)
        ->post(route('members.store'), batchSevenBAdminMemberPayload($email, $category->id, $level->id))
        ->assertRedirect(route('members.index'));

    $member = Member::where('email', $email)->firstOrFail();
    $user = $member->user()->firstOrFail();
    $url = null;

    Notification::assertSentTo($user, MemberInvitationNotification::class, function (MemberInvitationNotification $notification) use ($user, &$url): bool {
        $url = $notification->toMail($user)->actionUrl;

        return is_string($url) && str_contains($url, '/member-invitations/');
    });

    return compact('admin', 'category', 'level', 'member', 'user', 'url');
}

test('administrator-created members receive a pending account, application transaction, and one-time invitation', function () {
    ['admin' => $admin, 'member' => $member, 'user' => $user, 'url' => $url] = createBatchSevenBInvitedMember($this);
    $invitation = MemberInvitation::where('user_id', $user->id)->firstOrFail();
    $token = basename((string) parse_url($url, PHP_URL_PATH));

    expect($member->status)->toBe('pending')
        ->and($user->status)->toBe(User::STATUS_PENDING)
        ->and($user->role)->toBe(User::ROLE_MEMBER)
        ->and($user->member_id)->toBe($member->id)
        ->and($invitation->created_by_admin_id)->toBe($admin->id)
        ->and($invitation->token_hash)->not->toBe($token)
        ->and($member->transactions()->where('type', MembershipTransaction::TYPE_APPLICATION)->where('status', MembershipTransaction::STATUS_PENDING)->exists())->toBeTrue();
});

test('a valid member invitation sets the password and confirms the invited email once', function () {
    ['member' => $member, 'user' => $user, 'url' => $url] = createBatchSevenBInvitedMember($this, 'valid-invitation@example.test');

    $this->get($url)
        ->assertOk()
        ->assertSee('Set your')
        ->assertSee($user->email);

    $this->post($url, [
        'password' => 'ValidPassword123!',
        'password_confirmation' => 'ValidPassword123!',
    ])->assertRedirect(route('login.member'))
        ->assertSessionHas('success', 'Invitation accepted. Sign in using your new member account to complete the membership payment, then wait for administrator approval.');

    expect(Hash::check('ValidPassword123!', $user->fresh()->password))->toBeTrue()
        ->and($user->fresh()->email_verified_at)->not->toBeNull()
        ->and($user->fresh()->status)->toBe(User::STATUS_PENDING)
        ->and($member->fresh()->status)->toBe('pending')
        ->and(MemberInvitation::where('user_id', $user->id)->firstOrFail()->consumed_at)->not->toBeNull();
});

test('member invitation acceptance enforces the project password policy', function () {
    ['user' => $user, 'url' => $url] = createBatchSevenBInvitedMember($this, 'invalid-invitation@example.test');

    $this->post($url, [
        'password' => 'weak',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors('password');

    expect(MemberInvitation::where('user_id', $user->id)->firstOrFail()->consumed_at)->toBeNull()
        ->and($user->fresh()->email_verified_at)->toBeNull();
});

test('expired and reused member invitations are rejected', function () {
    ['user' => $expiredUser, 'url' => $expiredUrl] = createBatchSevenBInvitedMember($this, 'expired-invitation@example.test');
    MemberInvitation::where('user_id', $expiredUser->id)->update(['expires_at' => now()->subMinute()]);

    $this->get($expiredUrl)
        ->assertRedirect(route('login.member'))
        ->assertSessionHasErrors('email');

    ['user' => $reusedUser, 'url' => $reusedUrl] = createBatchSevenBInvitedMember($this, 'reused-invitation@example.test');
    $this->post($reusedUrl, [
        'password' => 'ValidPassword123!',
        'password_confirmation' => 'ValidPassword123!',
    ])->assertRedirect(route('login.member'));

    $this->get($reusedUrl)
        ->assertRedirect(route('login.member'))
        ->assertSessionHasErrors('email');

    expect($reusedUser->fresh()->email_verified_at)->not->toBeNull();
});

test('invitation tokens are not written to audit details or stored as plaintext', function () {
    ['user' => $user, 'url' => $url] = createBatchSevenBInvitedMember($this, 'secret-invitation@example.test');
    $token = basename((string) parse_url($url, PHP_URL_PATH));
    $audit = AuditLog::where('action', 'member.created')->latest('id')->firstOrFail();
    $details = json_encode($audit->details);

    expect($details)->not->toContain($token)
        ->and(MemberInvitation::where('user_id', $user->id)->firstOrFail()->token_hash)->toBe(hash('sha256', $token));
});

test('an invited unpaid member can reset the invitation password and continue to payment', function () {
    ['member' => $member, 'user' => $user, 'url' => $url] = createBatchSevenBInvitedMember($this, 'invited-reset@example.test');

    $this->post($url, [
        'password' => 'InitialPassword123!',
        'password_confirmation' => 'InitialPassword123!',
    ])->assertRedirect(route('login.member'));

    Notification::fake();

    $this->post(route('password.email.member'), [
        'email' => strtoupper($user->email),
    ])->assertRedirect()
        ->assertSessionHas('success', 'If that account exists for this role, a reset link has been sent.');

    $resetUrl = null;
    Notification::assertSentTo($user, RoleScopedResetPassword::class, function (RoleScopedResetPassword $notification) use ($user, &$resetUrl): bool {
        $mail = $notification->toMail($user);
        $resetUrl = $mail->actionUrl;

        return str_contains($resetUrl, '/reset-password/member/');
    });

    $resetToken = basename((string) parse_url($resetUrl, PHP_URL_PATH));

    $this->post(route('password.reset.member'), [
        'email' => $user->email,
        'password' => 'ResetPassword123!',
        'password_confirmation' => 'ResetPassword123!',
        'token' => $resetToken,
    ])->assertRedirect(route('login.member'));

    $this->post(route('login.member.submit'), [
        'email' => $user->email,
        'password' => 'ResetPassword123!',
    ])->assertRedirect(route('approval.pending'));

    $transaction = $member->transactions()->where('type', MembershipTransaction::TYPE_APPLICATION)->latest('id')->firstOrFail();

    $this->get(route('member.payment', $transaction))
        ->assertOk()
        ->assertSee('Confirm payment choice');

    expect($user->fresh()->status)->toBe(User::STATUS_PENDING)
        ->and($member->fresh()->status)->toBe('pending');
});
