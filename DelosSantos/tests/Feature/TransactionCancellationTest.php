<?php

use App\Models\AuditLog;
use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\User;
use App\Services\MembershipTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('cancelling an unpaid initial application releases the identity and preserves history', function () {
    $admin = User::factory()->admin()->create();
    $category = MembershipCategory::create(['name' => 'Cancellation Category', 'is_active' => true]);
    $level = MembershipLevel::create(['name' => 'Cancellation Level', 'fee' => 500, 'validity_months' => 12, 'is_active' => true]);
    $member = Member::create([
        'membership_number' => '1799999',
        'first_name' => 'Pending',
        'last_name' => 'Applicant',
        'email' => 'pending-applicant@example.test',
        'birth_date' => '1990-01-01',
        'membership_category_id' => $category->id,
        'membership_level_id' => $level->id,
        'status' => 'pending',
        'joined_at' => today(),
    ]);
    $user = User::factory()->create([
        'member_id' => $member->id,
        'name' => $member->full_name,
        'email' => $member->email,
        'role' => User::ROLE_MEMBER,
        'status' => User::STATUS_PENDING,
    ]);
    $transaction = app(MembershipTransactionService::class)->create([
        'member' => $member,
        'category' => $category,
        'level' => $level,
        'type' => MembershipTransaction::TYPE_APPLICATION,
    ]);

    Notification::fake();

    $this->actingAs($admin)
        ->post(route('admin.transactions.cancel', $transaction), [
            'cancel_reason' => 'The applicant did not continue with the membership payment.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Transaction cancelled. The unpaid pending member account was permanently deleted, and its email is available for a new registration.');

    $cancelled = $transaction->fresh();

    expect($cancelled->status)->toBe(MembershipTransaction::STATUS_CANCELLED)
        ->and($cancelled->cancelled_at)->not->toBeNull()
        ->and($cancelled->member_id)->toBeNull()
        ->and($cancelled->member_name_snapshot)->toBe('Pending Applicant')
        ->and($cancelled->member_email_snapshot)->toBe('pending-applicant@example.test')
        ->and($cancelled->membership_number_snapshot)->toBe('1799999')
        ->and(Member::whereKey($member->id)->exists())->toBeFalse()
        ->and(User::whereKey($user->id)->exists())->toBeFalse()
        ->and(AuditLog::where('action', 'member.identity_deleted_after_application_cancellation')->exists())->toBeTrue();

    $this->post(route('login.member.submit'), [
        'email' => 'pending-applicant@example.test',
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->post(route('register.member.submit'), [
        'first_name' => 'Replacement',
        'last_name' => 'Applicant',
        'email' => 'pending-applicant@example.test',
        'phone' => '09171234567',
        'birth_date' => '1990-01-01',
        'gender' => config('membership.genders')[0],
        'house_street' => '1 New Street',
        'barangay' => 'New Barangay',
        'city_municipality' => 'Makati City',
        'province' => 'Metro Manila',
        'zip_code' => '1200',
        'membership_category_id' => $category->id,
        'membership_level_id' => $level->id,
        'accepted' => '1',
        'password' => 'ReplacementPassword123!',
        'password_confirmation' => 'ReplacementPassword123!',
    ])->assertRedirect(route('login.member'));

    expect(Member::where('email', 'pending-applicant@example.test')->exists())->toBeTrue()
        ->and(User::where('email', 'pending-applicant@example.test')->where('role', User::ROLE_MEMBER)->exists())->toBeTrue();

    $this->actingAs($admin)
        ->get(route('admin.transactions.receipt', $transaction->fresh()))
        ->assertOk()
        ->assertSee('Transaction cancelled')
        ->assertSee($transaction->reference_number)
        ->assertSee($member->full_name)
        ->assertSee($member->email)
        ->assertSee($member->membership_number)
        ->assertSee($category->name)
        ->assertSee($level->name)
        ->assertSee('Transaction date')
        ->assertSee('Cancelled date');
});

test('admin member transaction history offers cancellation instead of member payment', function () {
    $admin = User::factory()->admin()->create();
    $category = MembershipCategory::create(['name' => 'History Cancellation Category', 'is_active' => true]);
    $level = MembershipLevel::create(['name' => 'History Cancellation Level', 'fee' => 500, 'validity_months' => 12, 'is_active' => true]);
    $member = Member::create([
        'membership_number' => '1799998',
        'first_name' => 'History',
        'last_name' => 'Applicant',
        'email' => 'history-applicant@example.test',
        'birth_date' => '1990-01-01',
        'membership_category_id' => $category->id,
        'membership_level_id' => $level->id,
        'status' => 'pending',
        'joined_at' => today(),
    ]);
    $user = User::factory()->create([
        'member_id' => $member->id,
        'name' => $member->full_name,
        'email' => $member->email,
        'role' => User::ROLE_MEMBER,
        'status' => User::STATUS_PENDING,
    ]);
    $transaction = app(MembershipTransactionService::class)->create([
        'member' => $member,
        'category' => $category,
        'level' => $level,
        'type' => MembershipTransaction::TYPE_APPLICATION,
    ]);

    Notification::fake();

    $this->actingAs($admin)
        ->get(route('admin.members.transactions', $member))
        ->assertOk()
        ->assertSee('Cancel')
        ->assertDontSee('Pay now')
        ->assertSee('data-cancel-trigger', false)
        ->assertSee(route('admin.transactions.cancel', $transaction), false);

    $this->actingAs($admin)
        ->post(route('admin.transactions.cancel', $transaction), ['cancel_reason' => 'The member did not proceed with payment.'])
        ->assertRedirect();

    $this->actingAs($admin)
        ->get(route('admin.members.transactions', $member))
        ->assertNotFound();

    $this->actingAs($admin)
        ->get(route('admin.transactions.index'))
        ->assertOk()
        ->assertSee('Cancelled record')
        ->assertSee($transaction->reference_number);

    expect(User::whereKey($user->id)->exists())->toBeFalse()
        ->and(Member::whereKey($member->id)->exists())->toBeFalse();
});

test('cancelling a paid initial application keeps the member identity and history', function () {
    $admin = User::factory()->admin()->create();
    $category = MembershipCategory::create(['name' => 'Paid Cancellation Category', 'is_active' => true]);
    $level = MembershipLevel::create(['name' => 'Paid Cancellation Level', 'fee' => 500, 'validity_months' => 12, 'is_active' => true]);
    $member = Member::create([
        'membership_number' => '1799997',
        'first_name' => 'Paid',
        'last_name' => 'Applicant',
        'email' => 'paid-applicant@example.test',
        'birth_date' => '1990-01-01',
        'membership_category_id' => $category->id,
        'membership_level_id' => $level->id,
        'status' => 'pending',
        'joined_at' => today(),
    ]);
    $user = User::factory()->create([
        'member_id' => $member->id,
        'name' => $member->full_name,
        'email' => $member->email,
        'role' => User::ROLE_MEMBER,
        'status' => User::STATUS_PENDING,
    ]);
    $service = app(MembershipTransactionService::class);
    $transaction = $service->create([
        'member' => $member,
        'category' => $category,
        'level' => $level,
        'type' => MembershipTransaction::TYPE_APPLICATION,
    ]);
    $service->recordFullPayment($transaction, MembershipTransaction::CHANNEL_GCASH, $user->id);

    Notification::fake();

    $this->actingAs($admin)
        ->post(route('admin.transactions.cancel', $transaction->fresh()), [
            'cancel_reason' => 'Payment reversal requires account review.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Transaction cancelled. The member application was automatically rejected.');

    expect($transaction->fresh()->status)->toBe(MembershipTransaction::STATUS_CANCELLED)
        ->and($member->fresh()->status)->toBe('inactive')
        ->and($user->fresh()->status)->toBe(User::STATUS_REJECTED)
        ->and(Member::whereKey($member->id)->exists())->toBeTrue()
        ->and(User::whereKey($user->id)->exists())->toBeTrue();
});

test('a legacy cancelled unpaid application can release its email during registration', function () {
    $category = MembershipCategory::create(['name' => 'Legacy Cancellation Category', 'is_active' => true]);
    $level = MembershipLevel::create(['name' => 'Legacy Cancellation Level', 'fee' => 500, 'validity_months' => 12, 'is_active' => true]);
    $member = Member::create([
        'membership_number' => '1799996',
        'first_name' => 'Legacy',
        'last_name' => 'Applicant',
        'email' => 'legacy-cancelled@example.test',
        'birth_date' => '1990-01-01',
        'membership_category_id' => $category->id,
        'membership_level_id' => $level->id,
        'status' => 'inactive',
        'joined_at' => today(),
    ]);
    $user = User::factory()->create([
        'member_id' => $member->id,
        'name' => $member->full_name,
        'email' => $member->email,
        'role' => User::ROLE_MEMBER,
        'status' => User::STATUS_REJECTED,
    ]);
    $transaction = app(MembershipTransactionService::class)->create([
        'member' => $member,
        'category' => $category,
        'level' => $level,
        'type' => MembershipTransaction::TYPE_APPLICATION,
    ]);
    $transaction->update([
        'status' => MembershipTransaction::STATUS_CANCELLED,
        'cancel_reason' => 'Legacy cancelled application.',
    ]);

    $this->post(route('login.member.submit'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email', 'We couldn’t sign you in. If you do not have an active account, please register.');

    Notification::fake();

    $this->post(route('register.member.submit'), [
        'first_name' => 'Replacement',
        'last_name' => 'Applicant',
        'email' => 'legacy-cancelled@example.test',
        'phone' => '09171234567',
        'birth_date' => '1990-01-01',
        'gender' => config('membership.genders')[0],
        'house_street' => '1 New Street',
        'barangay' => 'New Barangay',
        'city_municipality' => 'Makati City',
        'province' => 'Metro Manila',
        'zip_code' => '1200',
        'membership_category_id' => $category->id,
        'membership_level_id' => $level->id,
        'accepted' => '1',
        'password' => 'ReplacementPassword123!',
        'password_confirmation' => 'ReplacementPassword123!',
    ])->assertRedirect(route('login.member'));

    expect(Member::whereKey($member->id)->exists())->toBeFalse()
        ->and(User::whereKey($user->id)->exists())->toBeFalse()
        ->and(Member::where('email', 'legacy-cancelled@example.test')->count())->toBe(1)
        ->and(User::where('email', 'legacy-cancelled@example.test')->where('role', User::ROLE_MEMBER)->count())->toBe(1)
        ->and($transaction->fresh()->member_id)->toBeNull();
});
