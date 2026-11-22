<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('an expired csrf session returns the member login screen', function () {
    Route::post('/testing/session-expired', fn () => throw new TokenMismatchException('CSRF token mismatch.'));

    $response = $this->post('/testing/session-expired');

    $response->assertRedirect(route('login.member'))
        ->assertSessionHas('error', 'Your session expired. Please sign in again.');
});

test('a real role mismatch remains forbidden', function () {
    $member = User::factory()->member()->create();

    $this->actingAs($member)
        ->get('/members')
        ->assertForbidden();
});
