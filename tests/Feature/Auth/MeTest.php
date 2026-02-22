<?php

use App\Models\User;

it('requires jwt for /api/me', function () {
    $this->getJson('/api/me')->assertStatus(401);
});

it('returns profile for valid jwt', function () {
    User::query()->create([
        'name' => 'Pablo',
        'email' => 'pablo@example.com',
        'password' => 'testpassword',
    ]);

    $login = $this->postJson('/api/auth/login', [
        'email' => 'pablo@example.com',
        'password' => 'testpassword',
    ])->assertOk();

    $login->assertJsonStructure(['access_token']);

    $access = $login->json('access_token');

    expect($access)->toBeString();
    expect($access)->not->toBeEmpty();

    $this->withToken($access)
        ->getJson('/api/me')
        ->assertOk()
        ->assertJsonPath('data.email', 'pablo@example.com');
});

it('rejects invalid jwt on /api/me', function () {
    $this->withToken('invalid.token.value')
        ->getJson('/api/me')
        ->assertStatus(401);
});
