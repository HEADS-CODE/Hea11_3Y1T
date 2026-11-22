<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the member directory requires an administrator account', function () {
    $response = $this->get('/members');

    $response->assertRedirect(route('login.member'));
});
