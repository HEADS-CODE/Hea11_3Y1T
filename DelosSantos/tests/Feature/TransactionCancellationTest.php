<?php

use App\Models\AuditLog;
use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\User;
use App\Notifications\PortalNotification;
use App\Services\MembershipTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('cancelling an unpaid initial application rejects the account and removes it from approval', function () {
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
        ->assertSessionHas('success', 'Transaction cancelled. The member application was automatically rejected.');

    expect($transaction->fresh()->status)->toBe(MembershipTransaction::STATUS_CANCELLED)
        ->and($transaction->fresh()->cancelled_at)->not->toBeNull()
        ->and($user->fresh()->status)->toBe(User::STATUS_REJECTED)
        ->and($member->fresh()->status)->toBe('inactive')
        ->and(User::where('status', User::STATUS_PENDING)->whereKey($user->id)->exists())->toBeFalse()
        ->and(AuditLog::where('action', 'account.rejected')->where('auditable_id', $user->id)->exists())->toBeTrue();

    Notification::assertSentTo($user, PortalNotification::class, function (PortalNotification $notification) use ($user): bool {
        $data = $notification->toDatabase($user);

        return $data['title'] === 'Membership application rejected'
            && str_contains($data['message'], 'automatically rejected')
            && $data['url'] === route('login.member');
    });

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
