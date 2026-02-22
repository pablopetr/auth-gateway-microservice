<?php

use App\Models\User;

it('refresh rotates refresh token and invalidates the old one', function () {
    User::query()->create([
        'name' => 'Pablo',
        'email' => 'pablo@example.com',
        'password' => 'testpassword',
    ]);

    $login = $this->postJson('/api/auth/login', [
        'email' => 'pablo@example.com',
        'password' => 'testpassword',
    ])->assertOk();

    $oldRefresh = $login->json('tokens.refresh_token');

    $refresh = $this->postJson('/api/auth/refresh', [
        'refresh_token' => $oldRefresh,
    ])->assertOk();

    $newRefresh = $refresh->json('tokens.refresh_token');

    expect($newRefresh)->not->toBe($oldRefresh);

    $this->postJson('/api/auth/refresh', [
        'refresh_token' => $oldRefresh,
    ])->assertStatus(422);
});

it('fails refresh with invalid format', function () {
    $this->postJson('/api/auth/refresh', [
        'refresh_token' => 'invalid-format',
    ])->assertStatus(422);
});

it('logout revokes refresh token (cannot refresh afterwards)', function () {
    User::query()->create([
        'name' => 'Pablo',
        'email' => 'pablo@example.com',
        'password' => 'testpassword',
    ]);

    $login = $this->postJson('/api/auth/login', [
        'email' => 'pablo@example.com',
        'password' => 'testpassword',
    ])->assertOk();

    $refreshToken = $login->json('tokens.refresh_token');

    $this->postJson('/api/auth/logout', [
        'refresh_token' => $refreshToken,
    ])->assertOk()
        ->assertJson(['ok' => true]);

    $this->postJson('/api/auth/refresh', [
        'refresh_token' => $refreshToken,
    ])->assertStatus(422);
});
