<?php

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\User;
use App\Services\MembershipTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function batchSevenMemberPayload(string $email, int $categoryId, int $levelId, string $birthDate): array
{
    return [
        'first_name' => 'Batch',
        'last_name' => 'Seven',
        'email' => $email,
        'phone' => '',
        'birth_date' => $birthDate,
        'gender' => config('membership.genders')[0],
        'address' => 'Typed address',
        'house_street' => '1 Test Street',
        'barangay' => 'Test Barangay',
        'city_municipality' => 'Test City',
        'province' => 'Test Province',
        'zip_code' => '1000',
        'membership_category_id' => $categoryId,
        'membership_level_id' => $levelId,
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'accepted' => '1',
    ];
}

function batchSevenMemberFixture(string $email = 'batch-seven@example.test'): array
{
    $category = MembershipCategory::create(['name' => 'Batch Seven Student', 'is_active' => true]);
    $level = MembershipLevel::create(['name' => 'Batch Seven Basic', 'fee' => 500, 'validity_months' => 12, 'is_active' => true]);
    $member = Member::create([
        'membership_number' => '1799999',
        'first_name' => 'Batch',
        'last_name' => 'Member',
        'email' => $email,
        'birth_date' => '1990-01-01',
        'gender' => config('membership.genders')[0],
        'membership_category_id' => $category->id,
        'membership_level_id' => $level->id,
        'status' => 'pending',
        'joined_at' => today(),
    ]);
    $user = User::factory()->member($member)->pending()->create(['email' => $email, 'name' => $member->full_name]);
    $admin = User::factory()->admin()->create();

    return [$member, $user, $admin, $category, $level];
}

test('account approval uses the paid or already-approved application and ignores newer pending transactions', function () {
    [$member, $user, $admin, $category, $level] = batchSevenMemberFixture();
    $service = app(MembershipTransactionService::class);
    $application = $service->create(compact('member', 'category', 'level') + ['type' => MembershipTransaction::TYPE_APPLICATION]);
    $service->recordFullPayment($application, MembershipTransaction::CHANNEL_FRONT_DESK, $admin->id);
    $service->approve($application->fresh(), $admin->id);

    $service->create([
        'member' => $member,
        'category' => $category,
        'level' => $level,
        'type' => MembershipTransaction::TYPE_RENEWAL,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.approvals.approve', $user))
        ->assertSessionHas('success', 'Account approved.');

    expect($user->fresh()->status)->toBe(User::STATUS_ACTIVE)
        ->and($member->fresh()->status)->toBe('active')
        ->and($application->fresh()->status)->toBe(MembershipTransaction::STATUS_APPROVED);

    $this->actingAs($admin)
        ->post(route('admin.approvals.approve', $user->fresh()))
        ->assertSessionHas('success', 'This account is already approved.');
});

test('approving an initial application transaction sends the administrator to account approvals', function () {
    [$member, $user, $admin, $category, $level] = batchSevenMemberFixture('batch-seven-transaction@example.test');
    $service = app(MembershipTransactionService::class);
    $application = $service->create(compact('member', 'category', 'level') + ['type' => MembershipTransaction::TYPE_APPLICATION]);
    $service->recordFullPayment($application, MembershipTransaction::CHANNEL_FRONT_DESK, $admin->id);

    $this->actingAs($admin)
        ->post(route('admin.transactions.approve', $application))
        ->assertRedirect(route('admin.approvals.index'))
        ->assertSessionHas('success', 'Initial application payment approved. Review the member account approval next.');
});

test('member and administrator registration enforce minimum age and required privacy acceptance', function () {
    $category = MembershipCategory::create(['name' => 'Regular', 'is_active' => true]);
    $seniorCategory = MembershipCategory::create(['name' => 'Senior Citizen', 'is_active' => true]);
    $level = MembershipLevel::create(['name' => 'Batch Seven Level', 'fee' => 500, 'validity_months' => 12, 'is_active' => true]);

    $this->post(route('register.member.submit'), batchSevenMemberPayload(
        'underage-member@example.test',
        $category->id,
        $level->id,
        today()->subYears(17)->toDateString(),
    ))->assertSessionHasErrors('birth_date');

    $seniorPayload = batchSevenMemberPayload(
        'underage-senior@example.test',
        $seniorCategory->id,
        $level->id,
        today()->subYears(59)->toDateString(),
    );
    $this->post(route('register.member.submit'), $seniorPayload)->assertSessionHasErrors('birth_date');

    $missingConsent = batchSevenMemberPayload(
        'missing-consent@example.test',
        $category->id,
        $level->id,
        '1990-01-01',
    );
    unset($missingConsent['accepted']);
    $this->post(route('register.member.submit'), $missingConsent)->assertSessionHasErrors('accepted');

    $adminPayload = [
        'first_name' => 'Young',
        'last_name' => 'Administrator',
        'email' => 'underage-admin@example.test',
        'phone' => '09171234567',
        'birth_date' => today()->subYears(17)->toDateString(),
        'gender' => config('membership.genders')[0],
        'house_street' => '1 Test Street',
        'barangay' => 'Test Barangay',
        'city_municipality' => 'Test City',
        'province' => 'Test Province',
        'zip_code' => '1000',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'accepted' => '1',
    ];
    $this->post(route('register.admin.submit'), $adminPayload)->assertSessionHasErrors('birth_date');

    unset($adminPayload['birth_date']);
    $adminPayload['birth_date'] = '1990-01-01';
    unset($adminPayload['accepted']);
    $this->post(route('register.admin.submit'), $adminPayload)->assertSessionHasErrors('accepted');
});

test('administrator member create and edit enforce the same dynamic age rules', function () {
    $category = MembershipCategory::create(['name' => 'Batch Seven Category', 'is_active' => true]);
    $seniorCategory = MembershipCategory::create(['name' => 'Senior Citizen', 'is_active' => true]);
    $level = MembershipLevel::create(['name' => 'Batch Seven Level', 'fee' => 500, 'validity_months' => 12, 'is_active' => true]);
    $admin = User::factory()->admin()->create();

    $payload = batchSevenMemberPayload(
        'admin-created-underage@example.test',
        $category->id,
        $level->id,
        today()->subYears(17)->toDateString(),
    ) + ['status' => 'pending', 'joined_at' => today()->toDateString()];

    $this->actingAs($admin)
        ->post(route('members.store'), $payload)
        ->assertSessionHasErrors('birth_date');

    $member = Member::create([
        'membership_number' => '1799998',
        'first_name' => 'Existing',
        'last_name' => 'Member',
        'email' => 'existing-batch-seven@example.test',
        'birth_date' => '1990-01-01',
        'gender' => config('membership.genders')[0],
        'membership_category_id' => $category->id,
        'membership_level_id' => $level->id,
        'status' => 'active',
        'joined_at' => today(),
    ]);

    $editPayload = batchSevenMemberPayload(
        $member->email,
        $seniorCategory->id,
        $level->id,
        today()->subYears(59)->toDateString(),
    ) + ['status' => 'active', 'joined_at' => today()->toDateString()];

    $this->actingAs($admin)
        ->put(route('members.update', $member), $editPayload)
        ->assertSessionHasErrors('birth_date');
});
