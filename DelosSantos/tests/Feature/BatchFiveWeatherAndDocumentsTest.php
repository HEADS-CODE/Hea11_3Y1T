<?php

use App\Models\AdminProfile;
use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MemberRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function makeBatchFiveMember(string $email = 'batch-five-member@example.test'): array
{
    $category = MembershipCategory::create(['name' => 'Batch Five Student', 'is_active' => true]);
    $level = MembershipLevel::create(['name' => 'Batch Five Basic', 'fee' => 450, 'validity_months' => 12, 'is_active' => true]);
    $member = Member::create([
        'membership_number' => '1754321',
        'first_name' => 'Batch',
        'last_name' => 'Five',
        'gender' => 'Prefer not to say',
        'email' => $email,
        'membership_category_id' => $category->id,
        'membership_level_id' => $level->id,
        'status' => 'active',
        'joined_at' => now()->toDateString(),
    ]);
    $user = User::factory()->member($member)->create([
        'email' => $email,
        'email_verified_at' => now(),
    ]);

    return compact('category', 'level', 'member', 'user');
}

test('member and admin weather cards use their saved portal coordinates', function () {
    Http::fake(['https://api.openweathermap.org/*' => Http::response([
        'name' => 'Saved place',
        'main' => ['temp' => 27, 'humidity' => 70],
        'weather' => [['description' => 'clear sky', 'icon' => '01d']],
        'wind' => ['speed' => 1.5],
    ])]);
    config(['services.openweather.key' => 'batch-five-test-key']);

    ['member' => $member, 'user' => $memberUser] = makeBatchFiveMember();
    $member->update(['latitude' => 10.1234567, 'longitude' => 120.7654321]);

    $this->actingAs($memberUser)->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('data-latitude="10.1234567"', false)
        ->assertSee('data-longitude="120.7654321"', false);

    $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
    AdminProfile::create([
        'user_id' => $admin->id,
        'first_name' => 'Office',
        'last_name' => 'Admin',
        'gender' => 'Prefer not to say',
        'latitude' => 11.1111111,
        'longitude' => 122.2222222,
    ]);

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('data-latitude="11.1111111"', false)
        ->assertSee('data-longitude="122.2222222"', false);
});

test('weather card falls back to the configured safe location when no profile pin exists', function () {
    config([
        'membership.weather.default_latitude' => 14.5995,
        'membership.weather.default_longitude' => 120.9842,
    ]);
    ['user' => $user] = makeBatchFiveMember('fallback-weather@example.test');

    $this->actingAs($user)->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('data-latitude="14.5995"', false)
        ->assertSee('data-longitude="120.9842"', false)
        ->assertSee('Puihaha weather');
});

test('ordinary profile edits do not require supporting documents', function () {
    ['member' => $member, 'user' => $user] = makeBatchFiveMember('ordinary-profile@example.test');

    $this->actingAs($user)->post(route('member.profile-change.store'), [
        'first_name' => $member->first_name,
        'middle_name' => '',
        'last_name' => $member->last_name,
        'email' => $member->email,
        'phone' => '09171234567',
        'birth_date' => '',
        'gender' => $member->gender,
        'house_street' => '',
        'barangay' => '',
        'city_municipality' => '',
        'province' => '',
        'zip_code' => '',
    ])->assertRedirect(route('member.transactions'));

    expect(MemberRequest::where('request_type', MemberRequest::TYPE_PROFILE_CHANGE)->count())->toBe(1);
});

test('supporting documents use the private disk and never the public disk', function () {
    config(['filesystems.disks.member_private.root' => sys_get_temp_dir().'\\puihaha-batch-five']);
    ['member' => $member, 'user' => $user] = makeBatchFiveMember('private-document@example.test');
    $target = MembershipCategory::create(['name' => 'Private Document Category', 'is_active' => true]);

    $this->actingAs($user)->post(route('member.category-change.store'), [
        'membership_category_id' => $target->id,
        'privacy_consent' => '1',
        'documents' => [Illuminate\Http\UploadedFile::fake()->create('proof.pdf', 10, 'application/pdf')],
    ])->assertRedirect(route('member.transactions'));

    $document = $member->documents()->firstOrFail();
    expect($document->storage_path)->not->toBeEmpty()
        ->and($document->mime_type)->toBe('application/pdf')
        ->and(config('filesystems.disks.member_private.visibility'))->toBe('private')
        ->and(Storage::disk('member_private')->exists($document->storage_path))->toBeTrue();
});

