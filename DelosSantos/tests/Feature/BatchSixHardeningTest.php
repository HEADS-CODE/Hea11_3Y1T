<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

test('sensitive authentication endpoints use named throttles', function () {
    $expected = [
        'login.member.submit' => 'auth-login',
        'login.admin.submit' => 'auth-login',
        'register.member.submit' => 'auth-register',
        'register.admin.submit' => 'auth-register',
        'password.email.member' => 'password-reset',
        'password.email.admin' => 'password-reset',
        'password.reset' => 'password-reset',
        'password.reset.member' => 'password-reset',
        'password.reset.admin' => 'password-reset',
        'verification.resend' => 'verification-resend',
    ];

    foreach ($expected as $name => $limiter) {
        $route = collect(Route::getRoutes()->getRoutes())
            ->first(fn ($candidate) => $candidate->getName() === $name);

        expect($route)->not->toBeNull();
        expect($route->gatherMiddleware())->toContain('throttle:'.$limiter);
    }
});

test('runtime code keeps environment reads inside configuration files', function () {
    foreach (['app', 'routes'] as $directory) {
        foreach (File::allFiles(base_path($directory)) as $file) {
            expect($file->getContents())->not->toContain('env(');
        }
    }
});

test('themed production error views are present', function () {
    foreach ([404, 419, 429, 500] as $status) {
        expect(is_file(resource_path("views/errors/{$status}.blade.php")))->toBeTrue();
    }
});

test('supporting documents remain private and non-servable', function () {
    expect(config('filesystems.disks.member_private.visibility'))->toBe('private');
    expect(config('filesystems.disks.member_private.serve'))->toBeFalse();
    expect(config('filesystems.disks.member_private.root'))->not->toContain('public');
});
