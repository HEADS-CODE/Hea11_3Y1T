<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the member directory is publicly accessible', function () {
    $response = $this->get('/members');

    $response->assertOk()->assertSee('Registered members');
});
