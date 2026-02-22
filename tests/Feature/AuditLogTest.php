<?php

use App\Models\AuditLog;

it('writes audit logs for register/login/refresh/logout', function () {
    $this->postJson('/api/auth/register', [
        'name' => 'Pablo',
        'email' => 'pablo@gmail.com',
        'password' => 'testpassword',
    ])->assertCreated();

    expect(AuditLog::query()->where('event', 'auth.register')->exists())->toBeTrue();

    $login = $this->postJson('/api/auth/login', [
        'email' => 'pablo@gmail.com',
        'password' => 'testpassword',
    ])->assertOk();

    expect(AuditLog::query()->where('event', 'auth.login.success')->exists())->toBeTrue();

    $refreshToken = $login->json('refresh_token');

    $this->postJson('/api/auth/refresh', [
        'refresh_token' => $refreshToken,
    ])->assertOk();

    expect(AuditLog::query()->where('event', 'auth.refresh.success')->exists())->toBeTrue();
});
