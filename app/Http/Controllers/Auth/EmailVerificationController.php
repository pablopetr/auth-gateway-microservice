<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify(EmailVerificationRequest $request): \Illuminate\Http\JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['verified' => true]);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return response()->json(['verified' => true]);
    }

    public function resend(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        $user = \App\Models\User::query()->where('email', $data['email'])->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['sent' => false, 'reason' => 'already_verified']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['sent' => true]);
    }
}
