<?php

use App\Models\AuditLog;
use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('same email can have separate active member and admin accounts', function () {
    $email = 'same-email@example.test';
    $category = MembershipCategory::create(['name' => 'Test category', 'is_active' => true]);
    $level = MembershipLevel::create([
        'name' => 'Test level',
        'fee' => 399,
        'validity_months' => 1,
        'is_active' => true,
    ]);
    $member = Member::create([
        'membership_number' => '1700001',
        'first_name' => 'Test',
        'last_name' => 'Member',
        'email' => $email,
        'membership_category_id' => $category->id,
        'membership_level_id' => $level->id,
        'status' => 'active',
        'joined_at' => now()->toDateString(),
    ]);

    User::factory()->member($member)->create([
        'email' => $email,
    ]);

    User::factory()->admin()->create([
        'email' => $email,
    ]);

    $provider = app('auth')->createUserProvider('users');

    expect(User::where('email', $email)->count())->toBe(2)
        ->and($provider->retrieveByCredentials(['email' => $email, 'role' => User::ROLE_MEMBER]))->not->toBeNull()
        ->and($provider->retrieveByCredentials(['email' => $email, 'role' => User::ROLE_ADMIN]))->not->toBeNull();
});

test('disabled accounts are rejected by the role-aware provider', function () {
    $user = User::factory()->member()->create([
        'email' => 'disabled@example.test',
    ]);
    $user->forceFill(['status' => User::STATUS_DISABLED])->save();

    $provider = app('auth')->createUserProvider('users');

    expect($provider->retrieveByCredentials([
        'email' => $user->email,
        'role' => User::ROLE_MEMBER,
    ]))->toBeNull();
});

test('audit logs redact sensitive details and cannot be changed or deleted', function () {
    $auditLog = AuditLog::create([
        'action' => 'test.event',
        'details' => [
            'password' => 'never-store-this',
            'safe_value' => 'kept',
        ],
    ]);

    expect($auditLog->fresh()->details)->toMatchArray([
        'password' => '[REDACTED]',
        'safe_value' => 'kept',
    ]);

    expect(fn () => $auditLog->update(['action' => 'changed']))->toThrow(LogicException::class)
        ->and(fn () => $auditLog->delete())->toThrow(LogicException::class);
});
