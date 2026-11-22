<?php

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\Payment;
use App\Models\User;
use App\Services\MembershipTransactionService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function makeMilestoneFiveTransaction(string $email = 'transaction-member@example.test'): array
{
    $category = MembershipCategory::create(['name' => 'Transaction Student', 'is_active' => true]);
    $level = MembershipLevel::create(['name' => 'Transaction Gold', 'fee' => 575, 'validity_months' => 12, 'is_active' => true]);
    $member = Member::create([
        'membership_number' => '1712345',
        'first_name' => 'Transaction',
        'last_name' => 'Member',
        'email' => $email,
        'membership_category_id' => $category->id,
        'membership_level_id' => $level->id,
        'status' => 'pending',
        'joined_at' => now()->toDateString(),
    ]);
    $admin = User::factory()->admin()->create();

    return [$member, $category, $level, $admin];
}

test('only a paid transaction can be approved', function () {
    [$member, $category, $level, $admin] = makeMilestoneFiveTransaction();
    $service = app(MembershipTransactionService::class);
    $transaction = $service->create(compact('member', 'category', 'level') + ['type' => MembershipTransaction::TYPE_APPLICATION]);

    expect(fn () => $service->approve($transaction, $admin->id))->toThrow(ValidationException::class);

    $service->recordFullPayment($transaction, MembershipTransaction::CHANNEL_FRONT_DESK, $admin->id);
    $service->approve($transaction->fresh(), $admin->id);

    expect($transaction->fresh()->status)->toBe(MembershipTransaction::STATUS_APPROVED);
});

test('payments are created as one full level-fee payment', function () {
    [$member, $category, $level, $admin] = makeMilestoneFiveTransaction('payment-member@example.test');
    $transaction = app(MembershipTransactionService::class)->create(compact('member', 'category', 'level') + ['type' => MembershipTransaction::TYPE_APPLICATION]);
    $payment = app(MembershipTransactionService::class)->recordFullPayment($transaction, MembershipTransaction::CHANNEL_CARD, $admin->id);

    expect((float) $payment->amount)->toBe(575.0)
        ->and($transaction->fresh()->status)->toBe(MembershipTransaction::STATUS_PAID)
        ->and(Payment::where('membership_transaction_id', $transaction->id)->count())->toBe(1);

    expect(fn () => Payment::create([
        'membership_transaction_id' => $transaction->id,
        'amount' => 1,
        'payment_reference' => 'PAY-INVALID-AMOUNT',
        'payment_method' => MembershipTransaction::CHANNEL_FRONT_DESK,
        'status' => 'paid',
        'paid_at' => now(),
    ]))->toThrow(ValidationException::class);
});

test('every payment belongs to a valid transaction', function () {
    expect(fn () => Payment::create([
        'membership_transaction_id' => 999999,
        'amount' => 575,
        'payment_reference' => 'PAY-NO-TRANSACTION',
        'payment_method' => MembershipTransaction::CHANNEL_FRONT_DESK,
        'status' => 'paid',
        'paid_at' => now(),
    ]))->toThrow(QueryException::class);
});

test('a member has one selected category and level and transactions snapshot both', function () {
    [$member, $category, $level] = makeMilestoneFiveTransaction('assignment-member@example.test');
    $transaction = app(MembershipTransactionService::class)->create(compact('member', 'category', 'level') + ['type' => MembershipTransaction::TYPE_APPLICATION]);

    expect($member->fresh()->category->is($category))->toBeTrue()
        ->and($member->fresh()->level->is($level))->toBeTrue()
        ->and($transaction->membership_category_id)->toBe($category->id)
        ->and($transaction->membership_level_id)->toBe($level->id);
});

test('member registration generates distinct seven-character membership numbers beginning with 17', function () {
    $category = MembershipCategory::create(['name' => 'Registration Student', 'is_active' => true]);
    $level = MembershipLevel::create(['name' => 'Registration Basic', 'fee' => 399, 'validity_months' => 1, 'is_active' => true]);
    $payload = fn (string $email): array => [
        'first_name' => 'New', 'last_name' => 'Member', 'email' => $email,
        'birth_date' => '2000-01-01', 'gender' => 'Prefer not to say', 'address' => 'Typed address',
        'house_street' => '1 Test Street', 'barangay' => 'Test Barangay', 'city_municipality' => 'Test City',
        'province' => 'Test Province', 'zip_code' => '1000',
        'membership_category_id' => $category->id, 'membership_level_id' => $level->id,
        'password' => 'Password123!', 'password_confirmation' => 'Password123!',
    ];

    $this->post(route('register.member.submit'), $payload('first@example.test'))->assertRedirect();
    $this->post(route('register.member.submit'), $payload('second@example.test'))->assertRedirect();

    $numbers = Member::pluck('membership_number')->all();
    expect($numbers)->toHaveCount(2)
        ->and($numbers[0])->toMatch('/^17\d{5}$/')
        ->and($numbers[1])->toMatch('/^17\d{5}$/')
        ->and($numbers[0])->not->toBe($numbers[1]);
});

test('a member with transaction history is archived instead of hard deleted', function () {
    [$member, $category, $level, $admin] = makeMilestoneFiveTransaction('archived-member@example.test');
    $transaction = app(MembershipTransactionService::class)->create(compact('member', 'category', 'level') + ['type' => MembershipTransaction::TYPE_APPLICATION]);

    $this->actingAs($admin)->delete(route('members.destroy', $member))->assertRedirect(route('members.index'));

    expect(Member::find($member->id))->not->toBeNull()
        ->and(Member::find($member->id)->status)->toBe('inactive')
        ->and(Member::find($member->id)->archived_at)->not->toBeNull();
});

