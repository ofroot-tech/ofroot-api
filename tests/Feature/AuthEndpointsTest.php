<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers, logs in, returns me, and logs out', function () {
    // Register
    $register = $this->postJson('/api/auth/register', [
        'name' => 'Test User',
        'email' => 'user@example.com',
        'password' => 'password123',
    ])->assertCreated();

    $token = $register->json('token');
    expect($token)->toBeString()->not->toBeEmpty();

    // Me
    $me = $this->getJson('/api/auth/me', [
        'Authorization' => "Bearer {$token}",
        'Accept' => 'application/json',
    ])->assertOk();
    expect($me->json('data.email'))->toBe('user@example.com');

    // Logout
    $this->postJson('/api/auth/logout', [], [
        'Authorization' => "Bearer {$token}",
        'Accept' => 'application/json',
    ])->assertOk();

    // Login
    $login = $this->postJson('/api/auth/login', [
        'email' => 'user@example.com',
        'password' => 'password123',
    ])->assertOk();
    expect($login->json('token'))->toBeString()->not->toBeEmpty();
});
