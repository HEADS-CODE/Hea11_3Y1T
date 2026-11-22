<?php

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\User;
use App\Services\MembershipTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeMilestoneSixMember(string $email): array
{
    $category = MembershipCategory::create(['name' => 'Renewal Student', 'is_active' => true]);
    $basic = MembershipLevel::create(['name' => 'Renewal Basic', 'fee' => 425, 'validity_months' => 12, 'is_active' => true]);
    $member = Member::create([
        'membership_number' => '1765432',
        'first_name' => 'Renewal',
        'last_name' => 'Member',
        'email' => $email,
        'membership_category_id' => $category->id,
        'membership_level_id' => $basic->id,
        'status' => 'active',
        'joined_at' => now()->toDateString(),
    ]);
    $user = User::factory()->member($member)->create(['email' => $email]);
    $admin = User::factory()->admin()->create();

    return [$member, $category, $basic, $user, $admin];
}

test('a member can create a renewal transaction using the current level', function () {
    [$member, $category, $basic, $user] = makeMilestoneSixMember('renewal-request@example.test');

    $this->actingAs($user)->get(route('member.renew'))->assertOk()->assertSee('Renew your')->assertSee('membership.');
    $this->actingAs($user)->post(route('member.renew.store'))->assertRedirect(route('member.payment', $member->transactions()->latest()->first()));

    $transaction = $member->transactions()->latest()->first();
    expect($transaction->type)->toBe(MembershipTransaction::TYPE_RENEWAL)
        ->and($transaction->membership_category_id)->toBe($category->id)
        ->and($transaction->membership_level_id)->toBe($basic->id)
        ->and((float) $transaction->amount)->toBe(425.0);
});

test('a paid level-change transaction updates the member level after approval', function () {
    [$member, $category, $basic, $user, $admin] = makeMilestoneSixMember('level-change@example.test');
    $silver = MembershipLevel::create(['name' => 'Renewal Silver', 'fee' => 725, 'validity_months' => 12, 'is_active' => true]);

    $this->actingAs($user)->get(route('member.level-change'))->assertOk()->assertSee('Change your');
    $this->actingAs($user)->post(route('member.level-change.store'), ['membership_level_id' => $silver->id])
        ->assertRedirect(route('member.payment', $member->transactions()->latest()->first()));

    $transaction = $member->transactions()->latest()->first();
    app(MembershipTransactionService::class)->recordFullPayment($transaction, MembershipTransaction::CHANNEL_FRONT_DESK, $admin->id);
    $this->actingAs($admin)->post(route('admin.transactions.approve', $transaction))->assertRedirect();

    expect($transaction->fresh()->type)->toBe(MembershipTransaction::TYPE_LEVEL_CHANGE)
        ->and($member->fresh()->membership_category_id)->toBe($category->id)
        ->and($member->fresh()->membership_level_id)->toBe($silver->id)
        ->and($transaction->fresh()->status)->toBe(MembershipTransaction::STATUS_APPROVED);
});

test('renewal approval begins after an existing active membership ends', function () {
    [$member, $category, $basic, $user, $admin] = makeMilestoneSixMember('renewal-period@example.test');
    $service = app(MembershipTransactionService::class);

    $first = $service->create(['member' => $member, 'category' => $category, 'level' => $basic, 'type' => MembershipTransaction::TYPE_APPLICATION]);
    $service->recordFullPayment($first, MembershipTransaction::CHANNEL_FRONT_DESK, $admin->id);
    $service->approve($first->fresh(), $admin->id);
    $first = $first->fresh();

    $renewal = $service->create(['member' => $member->fresh(), 'category' => $category, 'level' => $basic, 'type' => MembershipTransaction::TYPE_RENEWAL]);
    $service->recordFullPayment($renewal, MembershipTransaction::CHANNEL_FRONT_DESK, $admin->id);
    $service->approve($renewal->fresh(), $admin->id);

    expect($renewal->fresh()->starts_at->toDateString())->toBe($first->ends_at->copy()->addDay()->toDateString());
});

