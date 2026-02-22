<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

it('registers a user, returns tokens, and sends verification email', function () {
    Notification::fake();

    $res = $this->postJson('/api/auth/register', [
        'name' => 'Pablo Eliezer',
        'email' => 'pablo@gmail.com',
        'password' => 'testpassword',
    ]);

    $res->assertCreated()
        ->assertJsonStructure([
            'tokens' => [
                'access_token',
                'token_type',
                'expires_in',
                'refresh_token',
                'refresh_token_expires_in',
            ],
            'user' => [
                'id',
                'name',
                'email',
                'email_verified_at',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJsonPath('user.email', 'pablo@gmail.com');

    $user = User::query()->where('email', 'pablo@gmail.com')->firstOrFail();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('rejects duplicate email on register', function () {
    User::query()->create([
        'name' => 'Pablo',
        'email' => 'pablo@example.com',
        'password' => 'testpassword',
    ]);

    $this->postJson('/api/auth/register', [
        'name' => 'Other',
        'email' => 'pablo@example.com',
        'password' => 'testpassword',
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});
