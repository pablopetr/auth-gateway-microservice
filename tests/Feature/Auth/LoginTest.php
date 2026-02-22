<?php

use App\Models\User;

it('logs in and returns tokens', function () {
    User::query()->create([
        'name' => 'Pablo',
        'email' => 'pablo@example.com',
        'password' => 'testpassword',
    ]);

    $res = $this->postJson('/api/auth/login', [
        'email' => 'pablo@example.com',
        'password' => 'testpassword',
    ]);

    $res->assertOk()
        ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'refresh_token',
                'refresh_token_expires_in',
        ]);
});

it('fails login with invalid credentials', function () {
    User::query()->create([
        'name' => 'Pablo',
        'email' => 'pablo@example.com',
        'password' => 'testpassword',
    ]);

    $this->postJson('/api/auth/login', [
        'email' => 'pablo@example.com',
        'password' => 'wrongpassword',
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('applies rate limit on login (throttle:login)', function () {
    User::query()->create([
        'name' => 'Pablo',
        'email' => 'pablo@example.com',
        'password' => 'testpassword',
    ]);

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/auth/login', [
            'email' => 'pablo@example.com',
            'password' => 'wrong',
        ])->assertStatus(422);
    }

    $this->postJson('/api/auth/login', [
        'email' => 'pablo@example.com',
        'password' => 'wrong',
    ])->assertStatus(429);
});
