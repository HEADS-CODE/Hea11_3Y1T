<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

test('administrator login lands on the dashboard and renders workflow navigation in order', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'batch-seven-c-admin@example.test',
    ]);

    $this->post(route('login.admin.submit'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Dashboard', 'Approvals', 'Members', 'Transactions', 'Tasks', 'Membership', 'Reports', 'Activity log'])
        ->assertSee(route('admin.approvals.index'))
        ->assertSee(route('admin.requests.index'))
        ->assertSee(route('categories.index'))
        ->assertSee(route('levels.index'));
});

test('remember me persists the role session and signing out clears the persistent credential', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'batch-seven-c-remember@example.test',
    ]);
    $recallerName = Auth::guard()->getRecallerName();

    $login = $this->post(route('login.admin.submit'), [
        'email' => $admin->email,
        'password' => 'password',
        'remember' => '1',
    ]);

    $login->assertRedirect(route('admin.dashboard'))->assertCookie($recallerName);
    expect($admin->fresh()->remember_token)->not->toBeNull();

    $logout = $this->post(route('logout'));

    $logout->assertRedirect(route('login.member'))->assertCookieExpired($recallerName);
    expect($admin->fresh()->remember_token)->toBeNull();
});
