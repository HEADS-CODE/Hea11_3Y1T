<?php

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskPortalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeActivePortalMember(string $email, string $number): array
{
    $category = MembershipCategory::firstOrCreate(['name' => 'Portal Student'], ['is_active' => true]);
    $level = MembershipLevel::firstOrCreate(['name' => 'Portal Basic'], ['fee' => 399, 'validity_months' => 1, 'is_active' => true]);
    $member = Member::create([
        'membership_number' => $number,
        'first_name' => 'Portal',
        'last_name' => 'Member',
        'email' => $email,
        'membership_category_id' => $category->id,
        'membership_level_id' => $level->id,
        'status' => 'active',
        'joined_at' => now()->toDateString(),
    ]);
    $user = User::factory()->member($member)->create(['email' => $email]);
    $user->forceFill(['email_verified_at' => now()])->save();

    return [$member, $user];
}

test('administrators assign tasks and members can only complete their own assignments', function () {
    [$memberOne, $userOne] = makeActivePortalMember('one@example.test', '1700001');
    [$memberTwo, $userTwo] = makeActivePortalMember('two@example.test', '1700002');
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.tasks.store'), [
        'member_id' => $memberOne->id,
        'task_title' => 'Member one task',
        'description' => 'Administrator instructions',
        'deadline' => now()->addDays(5)->format('Y-m-d H:i'),
    ])->assertRedirect(route('admin.tasks.index'));

    $task = Task::first();
    expect($task->member_id)->toBe($memberOne->id);
    expect($task->created_by_user_id)->toBe($admin->id);

    $this->actingAs($userOne)->post('/tasks', [
        'task_title' => 'Member-created task',
    ])->assertStatus(405);

    $this->actingAs($userTwo)->get(route('tasks.index'))
        ->assertOk()
        ->assertDontSee('Member one task');

    $this->actingAs($userTwo)->patch(route('tasks.complete', $task))->assertNotFound();
    $this->actingAs($userOne)->patch(route('tasks.complete', $task))->assertRedirect();
    expect($task->fresh()->completed)->toBeTrue();
});

test('members cannot manage administrator task routes', function () {
    [$member, $memberUser] = makeActivePortalMember('member-admin-boundary@example.test', '1700003');

    $this->actingAs($memberUser)->get(route('admin.tasks.index'))
        ->assertForbidden()
        ->assertSee('This page belongs to another portal');
    $this->actingAs($memberUser)->get(route('admin.tasks.create'))->assertForbidden();
    $this->actingAs($memberUser)->post(route('admin.tasks.store'), [
        'member_id' => $member->id,
        'task_title' => 'Not allowed',
    ])->assertForbidden();
});

test('administrator task pages render the assignment register and form', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.tasks.index'))
        ->assertOk()
        ->assertSee('Learning tasks')
        ->assertSee('Assign task');

    $this->actingAs($admin)->get(route('admin.tasks.create'))
        ->assertOk()
        ->assertSee('Assign a')
        ->assertSee('Assign to member')
        ->assertSee('Priority');
});

test('administrators can classify tasks by priority and filter the register', function () {
    [$member] = makeActivePortalMember('priority-member@example.test', '1700008');
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.tasks.store'), [
        'member_id' => $member->id,
        'task_title' => 'High priority activity',
        'description' => 'Complete this first.',
        'priority' => Task::PRIORITY_HIGH,
    ])->assertRedirect();

    $this->actingAs($admin)->post(route('admin.tasks.store'), [
        'member_id' => $member->id,
        'task_title' => 'Low priority activity',
        'description' => 'Complete this later.',
        'priority' => Task::PRIORITY_LOW,
    ])->assertRedirect();

    expect(Task::where('task_title', 'High priority activity')->value('priority'))->toBe(Task::PRIORITY_HIGH);

    $this->actingAs($admin)->get(route('admin.tasks.index', ['priority' => Task::PRIORITY_HIGH]))
        ->assertOk()
        ->assertSee('High priority activity')
        ->assertDontSee('Low priority activity');
});

test('member task completion supports JSON updates for live toggles', function () {
    [$member, $memberUser] = makeActivePortalMember('ajax-task@example.test', '1700009');
    $admin = User::factory()->admin()->create();
    $task = Task::create([
        'member_id' => $member->id,
        'created_by_user_id' => $admin->id,
        'task_title' => 'AJAX completion task',
        'priority' => Task::PRIORITY_HIGH,
        'status' => Task::STATUS_ASSIGNED,
        'published_at' => now(),
    ]);

    $this->actingAs($memberUser)->patchJson(route('tasks.complete', $task))
        ->assertOk()
        ->assertJsonPath('task.id', $task->id)
        ->assertJsonPath('task.completed', true)
        ->assertJsonPath('summary.completed', 1);

    expect($task->fresh()->completed)->toBeTrue();
});

test('member portal has separate home, task, membership, category, and level pages', function () {
    [, $memberUser] = makeActivePortalMember('member-navigation@example.test', '1700010');

    $this->actingAs($memberUser)->get(route('tasks.index'))->assertOk()->assertSee('Priority &amp; immediate action', false)->assertSee('Home')->assertSee('My Tasks')->assertSee('My Membership')->assertSee('Categories')->assertSee('Levels')->assertSee('History');
    $this->actingAs($memberUser)->get(route('member.tasks'))->assertOk()->assertSee('My <em>tasks.</em>', false);
    $this->actingAs($memberUser)->get(route('member.membership'))->assertOk()->assertSee('My <em>membership.</em>', false);
    $this->actingAs($memberUser)->get(route('member.categories'))->assertOk()->assertSee('Member <em>categories.</em>', false);
    $this->actingAs($memberUser)->get(route('member.levels'))->assertOk()->assertSee('Membership <em>levels.</em>', false);
});

test('auth pages use concise role-specific hero and eyebrow language', function () {
    $this->get(route('login.member'))->assertOk()->assertSee('<strong>Member</strong> portal', false);
    $this->get(route('login.admin'))->assertOk()->assertSee('<strong>Admin</strong> portal', false);
    $this->get(route('register.member'))->assertOk()->assertSee('Begin your<br><em>next chapter.</em>', false);
    $this->get(route('register.admin'))->assertOk()->assertSee('Guide the<br><em>learning journey.</em>', false)->assertSee('New <strong>administrator</strong>', false);
});

test('administrators can assign one task to every active member', function () {
    [$memberOne] = makeActivePortalMember('bulk-one@example.test', '1700006');
    [$memberTwo] = makeActivePortalMember('bulk-two@example.test', '1700007');
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.tasks.store'), [
        'member_id' => 'all',
        'task_title' => 'Shared learning activity',
        'description' => 'Complete this activity before the deadline.',
        'deadline' => now()->addDays(4)->format('Y-m-d H:i'),
    ])->assertRedirect(route('admin.tasks.index'));

    expect(Task::where('task_title', 'Shared learning activity')->pluck('member_id')->all())
        ->toEqualCanonicalizing([$memberOne->id, $memberTwo->id]);
});

test('members can request a deadline extension and administrators can approve or reject it', function () {
    [$member, $memberUser] = makeActivePortalMember('extension-member@example.test', '1700004');
    $admin = User::factory()->admin()->create();
    $task = Task::create([
        'member_id' => $member->id,
        'created_by_user_id' => $admin->id,
        'task_title' => 'Learning task with a deadline',
        'description' => 'Complete the activity.',
        'status' => Task::STATUS_ASSIGNED,
        'deadline' => now()->addDays(2),
        'published_at' => now(),
    ]);

    $newDeadline = now()->addDays(7)->format('Y-m-d H:i');
    $this->actingAs($memberUser)->post(route('tasks.extension-requests.store', $task), [
        'reason' => 'I need additional time to complete the activity carefully.',
    ])->assertRedirect();

    $extension = $task->extensionRequests()->first();
    expect($extension)->not->toBeNull()->and($extension->status)->toBe('pending');

    $this->actingAs($admin)->post(route('admin.task-extension-requests.approve', $extension), [
        'new_deadline' => $newDeadline,
        'decision_note' => 'Approved for this learning activity.',
    ])->assertRedirect();

    expect($extension->fresh()->status)->toBe('approved')
        ->and($task->fresh()->deadline->format('Y-m-d H:i'))->toBe($newDeadline);
});

test('insights use three visible chart rows', function () {
    [, $memberUser] = makeActivePortalMember('insights-member@example.test', '1700005');

    $this->actingAs($memberUser)->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('id="insights"', false)
        ->assertSee('Completion status')
        ->assertSee('Assignments in the last 7 days')
        ->assertSee('Completions in the last 14 days');
});

test('task notifications keep member and administrator destinations separated', function () {
    $member = User::factory()->member()->create();
    $admin = User::factory()->admin()->create();

    $memberNotification = new TaskPortalNotification('New task assigned', 'A task was assigned.', route('tasks.index'));
    $adminNotification = new TaskPortalNotification('Task completed', 'A task was completed.', route('admin.tasks.index'));

    expect($memberNotification->toDatabase($member)['url'])->toBe(route('tasks.index'))
        ->and($adminNotification->toDatabase($admin)['url'])->toBe(route('admin.tasks.index'))
        ->and($memberNotification->via($member))->toBe(['database'])
        ->and($adminNotification->via($admin))->toBe(['database']);
});
