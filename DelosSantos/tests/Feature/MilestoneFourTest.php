<?php

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\Task;
use App\Models\User;
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

test('members can only create and see their own tasks', function () {
    [$memberOne, $userOne] = makeActivePortalMember('one@example.test', '1700001');
    [$memberTwo, $userTwo] = makeActivePortalMember('two@example.test', '1700002');

    $this->actingAs($userOne)->post(route('tasks.store'), [
        'task_title' => 'Member one task',
        'description' => 'Private task',
    ])->assertRedirect(route('tasks.index'));

    $task = Task::first();
    expect($task->member_id)->toBe($memberOne->id);

    $this->actingAs($userTwo)->get(route('tasks.index'))
        ->assertOk()
        ->assertDontSee('Member one task');

    $this->actingAs($userTwo)->patch(route('tasks.complete', $task))->assertNotFound();
    $this->actingAs($userOne)->patch(route('tasks.complete', $task))->assertRedirect();
    expect($task->fresh()->completed)->toBeTrue();
});
