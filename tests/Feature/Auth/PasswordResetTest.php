<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

it('sends password reset link notification', function () {
    Notification::fake();

    $user = User::query()->create([
        'name' => 'Pablo',
        'email' => 'pablo@example.com',
        'password' => 'testpassword',
    ]);

    $this->postJson('/api/auth/forgot-password', [
        'email' => 'pablo@example.com',
    ])->assertOk()
        ->assertJson(['sent' => true]);

    Notification::assertSentTo($user, ResetPassword::class);
});

it('resets password and allows login with new password', function () {
    $user = User::query()->create([
        'name' => 'Pablo',
        'email' => 'pablo@example.com',
        'password' => 'testpassword',
    ]);

    $token = Password::broker()->createToken($user);

    $this->postJson('/api/auth/reset-password', [
        'token' => $token,
        'email' => 'pablo@example.com',
        'password' => 'newpassword123',
    ])->assertOk()
        ->assertJson(['reset' => true]);

    $this->postJson('/api/auth/login', [
        'email' => 'pablo@example.com',
        'password' => 'testpassword',
    ])->assertStatus(422);

    $this->postJson('/api/auth/login', [
        'email' => 'pablo@example.com',
        'password' => 'newpassword123',
    ])->assertOk();
});
