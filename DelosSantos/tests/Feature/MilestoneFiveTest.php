<?php

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\MemberDocument;
use App\Models\MemberRequest;
use App\Models\Payment;
use App\Models\User;
use App\Services\MembershipTransactionService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

test('permanently deleting a member preserves immutable transaction history for administrators', function () {
    Storage::fake('member_private');
    [$member, $category, $level, $admin] = makeMilestoneFiveTransaction('deleted-member@example.test');
    $memberUser = User::factory()->member($member)->create(['email' => $member->email]);
    $transaction = app(MembershipTransactionService::class)->create(compact('member', 'category', 'level') + ['type' => MembershipTransaction::TYPE_APPLICATION]);
    $payment = app(MembershipTransactionService::class)->recordFullPayment($transaction, MembershipTransaction::CHANNEL_CARD, $admin->id);
    $memberRequest = $member->requests()->create([
        'submitted_by_user_id' => $memberUser->id,
        'request_type' => MemberRequest::TYPE_PROFILE_CHANGE,
        'source' => MemberRequest::SOURCE_ONLINE,
        'status' => MemberRequest::STATUS_PENDING,
        'previous_values' => ['first_name' => $member->first_name],
        'proposed_values' => ['first_name' => 'Changed'],
        'consent_at' => now(),
    ]);
    Storage::disk('member_private')->put('member-test.txt', 'private');
    MemberDocument::create([
        'member_request_id' => $memberRequest->id,
        'member_id' => $member->id,
        'uploaded_by_user_id' => $memberUser->id,
        'document_type' => 'profile_support',
        'original_name' => 'member-test.txt',
        'storage_path' => 'member-test.txt',
        'mime_type' => 'text/plain',
        'extension' => 'txt',
        'size_bytes' => 7,
        'privacy_consented_at' => now(),
    ]);

    $this->actingAs($admin)->delete(route('members.destroy', $member), [
        'confirm_name' => $member->full_name,
    ])->assertRedirect(route('members.index'));

    $preserved = $transaction->fresh();
    expect(Member::find($member->id))->toBeNull()
        ->and(User::find($memberUser->id))->toBeNull()
        ->and($preserved)->not->toBeNull()
        ->and($preserved->member_id)->toBeNull()
        ->and($preserved->member_name_snapshot)->toBe('Transaction Member')
        ->and($preserved->membership_number_snapshot)->toBe('1712345')
        ->and($preserved->level_name_snapshot)->toBe('Transaction Gold')
        ->and(Payment::find($payment->id))->not->toBeNull()
        ->and(MemberRequest::find($memberRequest->id))->toBeNull()
        ->and(Storage::disk('member_private')->exists('member-test.txt'))->toBeFalse();
});

