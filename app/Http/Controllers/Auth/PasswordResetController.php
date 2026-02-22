<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final class PasswordResetController extends Controller
{
    public function forgot(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink(['email' => $data['email']]);

        return response()->json([
            'sent' => $status === Password::RESET_LINK_SENT,
        ]);
    }

    public function reset(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $status = Password::reset(
            ['email' => $data['email'], 'password' => $data['password'], 'token' => $data['token']],
            function ($user, $password) {
                $user->forceFill(['password' => $password])->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        return response()->json([
            'reset' => $status === Password::PASSWORD_RESET,
        ]);
    }
}
