<?php

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\Task;
use App\Models\TaskExtensionRequest;
use App\Models\User;
use App\Notifications\PortalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeBatchFourMember(string $email): array
{
    $category = MembershipCategory::create(['name' => 'Batch Four Student', 'is_active' => true]);
    $otherCategory = MembershipCategory::create(['name' => 'Batch Four Regular', 'is_active' => true]);
    $level = MembershipLevel::create(['name' => 'Batch Four Basic', 'fee' => 425, 'validity_months' => 12, 'benefits' => 'Foundational learning benefits.', 'is_active' => true]);
    $higherLevel = MembershipLevel::create(['name' => 'Batch Four Gold', 'fee' => 999, 'validity_months' => 12, 'benefits' => 'Priority learning benefits and advanced access.', 'is_active' => true]);
    $member = Member::create([
        'membership_number' => '1765438',
        'first_name' => 'Batch',
        'last_name' => 'Four',
        'email' => $email,
        'membership_category_id' => $category->id,
        'membership_level_id' => $level->id,
        'status' => 'active',
        'joined_at' => now()->toDateString(),
    ]);
    $user = User::factory()->member($member)->create(['email' => $email, 'email_verified_at' => now()]);

    return compact('member', 'category', 'otherCategory', 'level', 'higherLevel', 'user');
}

test('level and category catalog links prefill self-service forms', function () {
    ['member' => $member, 'higherLevel' => $higherLevel, 'otherCategory' => $otherCategory, 'user' => $user] = makeBatchFourMember('batch-four-prefill@example.test');

    $this->actingAs($user)->get(route('member.levels'))->assertOk()->assertSee(route('member.level-change', ['level' => $higherLevel->id]));
    $this->actingAs($user)->get(route('member.level-change', ['level' => $higherLevel->id]))
        ->assertOk()
        ->assertSee('selected', false)
        ->assertSee($higherLevel->benefits);

    $this->actingAs($user)->get(route('member.categories'))->assertOk()->assertSee(route('member.category-change', ['category' => $otherCategory->id]));
    $this->actingAs($user)->get(route('member.category-change', ['category' => $otherCategory->id]))
        ->assertOk()
        ->assertSee('value="'.$otherCategory->id.'" selected', false);
});

test('my requests includes task extension status and keeps documents member-private', function () {
    ['member' => $member, 'user' => $user] = makeBatchFourMember('batch-four-requests@example.test');
    $admin = User::factory()->admin()->create();
    $task = Task::create([
        'member_id' => $member->id,
        'created_by_user_id' => $admin->id,
        'task_title' => 'Task extension subject',
        'description' => 'A task for request history.',
        'status' => Task::STATUS_ASSIGNED,
        'published_at' => now(),
    ]);
    TaskExtensionRequest::create([
        'task_id' => $task->id,
        'member_id' => $member->id,
        'reason' => 'Additional time is needed.',
        'status' => TaskExtensionRequest::STATUS_PENDING,
    ]);

    $this->actingAs($user)->get(route('member.requests.index'))
        ->assertOk()
        ->assertSee('Task extension')
        ->assertSee('Task extension subject')
        ->assertSee('Pending');
});

test('notification destinations fall back to the active portal when a route has the wrong role', function () {
    $member = User::factory()->member()->create();
    $admin = User::factory()->admin()->create();

    expect((new PortalNotification('Update', 'Message', route('admin.dashboard')))->toDatabase($member)['url'])->toBe(route('tasks.index'))
        ->and((new PortalNotification('Update', 'Message', route('tasks.index')))->toDatabase($admin)['url'])->toBe(route('admin.dashboard'));
});

test('admin dashboard and transaction pages expose immutable historical email snapshots', function () {
    ['member' => $member, 'user' => $user] = makeBatchFourMember('historical-snapshot@example.test');
    $admin = User::factory()->admin()->create();
    $transaction = \App\Models\MembershipTransaction::create([
        'member_id' => $member->id,
        'member_name_snapshot' => $member->full_name,
        'member_email_snapshot' => 'old-address@example.test',
        'membership_number_snapshot' => $member->membership_number,
        'category_name_snapshot' => $member->category->name,
        'level_name_snapshot' => $member->level->name,
        'membership_category_id' => $member->membership_category_id,
        'membership_level_id' => $member->membership_level_id,
        'reference_number' => 'TXN-BATCH4-SNAPSHOT',
        'type' => \App\Models\MembershipTransaction::TYPE_APPLICATION,
        'status' => \App\Models\MembershipTransaction::STATUS_PENDING,
        'amount' => $member->level->fee,
    ]);

    $member->update(['email' => 'new-address@example.test']);
    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('old-address@example.test');
    $this->actingAs($admin)->get(route('admin.transactions.index'))->assertOk()->assertSee('old-address@example.test');
    expect($transaction->fresh()->member_email_snapshot)->toBe('old-address@example.test');
});
