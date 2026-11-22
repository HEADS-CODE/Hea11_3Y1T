<?php

use App\Models\Member;
use App\Models\MemberDocument;
use App\Models\MemberRequest;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function makeBatchBMember(string $email = 'batch-b-member@example.test', string $status = 'active'): array
{
    $category = MembershipCategory::create(['name' => 'Batch B Student', 'is_active' => true]);
    $otherCategory = MembershipCategory::create(['name' => 'Batch B Regular', 'is_active' => true]);
    $level = MembershipLevel::create(['name' => 'Batch B Basic', 'fee' => 450, 'validity_months' => 12, 'is_active' => true]);
    $member = Member::create([
        'membership_number' => '1798765',
        'first_name' => 'Batch',
        'last_name' => 'Member',
        'gender' => 'Prefer not to say',
        'email' => $email,
        'membership_category_id' => $category->id,
        'membership_level_id' => $level->id,
        'status' => $status,
        'joined_at' => now()->toDateString(),
    ]);
    $user = User::factory()->member($member)->create([
        'email' => $email,
        'password' => Hash::make('Password123!'),
        'email_verified_at' => now(),
    ]);
    $admin = User::factory()->admin()->create();

    return compact('member', 'category', 'otherCategory', 'level', 'user', 'admin');
}

test('inactive member credentials reach only the reactivation screen', function () {
    ['user' => $user] = makeBatchBMember('inactive-batch@example.test', 'inactive');

    $this->post(route('login.member.submit'), ['email' => $user->email, 'password' => 'Password123!'])
        ->assertRedirect(route('account.inactive'));
    $this->assertAuthenticatedAs($user);
    $this->get(route('tasks.index'))->assertRedirect(route('account.inactive'));
    $this->get(route('account.inactive'))->assertOk()->assertSee('Your account is');
});

test('category-change documents stay private and administrators can review them', function () {
    config(['filesystems.disks.member_private.root' => sys_get_temp_dir().'\\puihaha-batch-b']);
    ['member' => $member, 'otherCategory' => $otherCategory, 'user' => $user, 'admin' => $admin] = makeBatchBMember();

    $this->actingAs($user)->post(route('member.category-change.store'), [
        'membership_category_id' => $otherCategory->id,
        'privacy_consent' => '1',
        'documents' => [UploadedFile::fake()->create('eligibility.pdf', 20, 'application/pdf')],
    ])->assertRedirect(route('member.transactions'));

    $memberRequest = MemberRequest::firstOrFail();
    $document = MemberDocument::firstOrFail();
    expect(Storage::disk('member_private')->exists($document->storage_path))->toBeTrue();

    $this->actingAs($user)->get(route('admin.requests.documents.download', [$memberRequest, $document->id]))->assertForbidden();
    $this->actingAs($admin)->get(route('admin.requests.documents.download', [$memberRequest, $document->id]))->assertOk();
    $this->actingAs($admin)->post(route('admin.requests.approve', $memberRequest))->assertRedirect();

    expect($member->fresh()->membership_category_id)->toBe($otherCategory->id)
        ->and($memberRequest->fresh()->status)->toBe(MemberRequest::STATUS_APPROVED)
        ->and($user->fresh()->unreadNotifications()->count())->toBe(1);
});

test('profile name changes require a supporting document and privacy consent', function () {
    ['member' => $member, 'user' => $user] = makeBatchBMember('profile-batch@example.test');

    $this->actingAs($user)->post(route('member.profile-change.store'), [
        'first_name' => 'Changed',
        'last_name' => $member->last_name,
        'email' => $member->email,
        'gender' => $member->gender,
    ])->assertSessionHasErrors('documents');
    expect(MemberRequest::count())->toBe(0);
});

test('member and administrator request pages render', function () {
    ['user' => $user, 'admin' => $admin] = makeBatchBMember('pages-batch@example.test');

    $this->actingAs($user)->get(route('member.category-change'))->assertOk()->assertSee('Change your');
    $this->actingAs($user)->get(route('member.profile-change'))->assertOk()->assertSee('Edit your');
    $this->actingAs($user)->get(route('member.requests.index'))->assertOk()->assertSee('My');
    $this->actingAs($admin)->get(route('admin.requests.index'))->assertOk()->assertSee('Member');
    $this->actingAs($admin)->get(route('admin.activity.index'))->assertOk()->assertSee('Activity');
});

test('administrators can view a member document while members cannot', function () {
    config(['filesystems.disks.member_private.root' => sys_get_temp_dir().'\\puihaha-document-view']);
    ['member' => $member, 'user' => $user, 'admin' => $admin] = makeBatchBMember('document-view@example.test');
    $memberRequest = $member->requests()->create([
        'submitted_by_user_id' => $user->id,
        'request_type' => MemberRequest::TYPE_PROFILE_CHANGE,
        'source' => MemberRequest::SOURCE_ONLINE,
        'status' => MemberRequest::STATUS_APPROVED,
        'consent_at' => now(),
    ]);
    Storage::disk('member_private')->put('identity.pdf', '%PDF-1.4 test');
    $document = MemberDocument::create([
        'member_request_id' => $memberRequest->id,
        'member_id' => $member->id,
        'uploaded_by_user_id' => $user->id,
        'document_type' => 'identity',
        'original_name' => 'identity.pdf',
        'storage_path' => 'identity.pdf',
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'size_bytes' => 13,
        'privacy_consented_at' => now(),
    ]);
    expect(Storage::disk('member_private')->exists($document->storage_path))->toBeTrue();

    $this->actingAs($admin)
        ->get(route('members.documents.index', $member))
        ->assertOk()
        ->assertSee('identity.pdf');
    $this->actingAs($user)
        ->get(route('members.documents.index', $member))
        ->assertForbidden();
    $this->actingAs($admin)
        ->get(route('members.documents.view', [$member, $document]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});
