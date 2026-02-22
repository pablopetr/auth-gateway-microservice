<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\DTO\User\UserProfileDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, AuthService $auth): JsonResponse
    {
        $dto = RegisterDTO::fromArray($request->validated());
        $tokens = $auth->register($dto->name, $dto->email, $dto->password, $request->userAgent(), $request->userAgent());

        auth()->user();
        $user = User::query()->where('email', $dto->email)->firstOrFail();

        return response()->json([
            'tokens' => $tokens->toArray(),
            'user' => $user->toArray(),
        ], 201);
    }

    public function login(LoginRequest $request, AuthService $auth): \Illuminate\Http\JsonResponse
    {
        $dto = LoginDTO::fromArray($request->validated());
        $tokens = $auth->login($dto->email, $dto->password, $request->ip(), $request->userAgent());

        $user = \App\Models\User::query()->where('email', $dto->email)->firstOrFail();

        return response()->json([
            'tokens' => $tokens->toArray(),
            'user' => UserProfileDTO::fromUser($user)->toArray(),
        ]);
    }

    public function refresh(Request $request, AuthService $auth): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        $tokens = $auth->refresh($data['refresh_token'], $request->ip(), $request->userAgent());

        return response()->json(['tokens' => $tokens->toArray()]);
    }

    public function logout(Request $request, AuthService $auth): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        $auth->logout($data['refresh_token'], $request->ip(), $request->userAgent());

        return response()->json(['ok' => true]);
    }
}
