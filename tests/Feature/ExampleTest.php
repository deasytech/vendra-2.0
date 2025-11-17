<?php

use Tests\TestCase;

test('returns a successful response', function () {
    $response = $this->get('/');
    $response->assertRedirect('/login');
})->uses(TestCase::class);
