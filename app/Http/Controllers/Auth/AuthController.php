<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, AuthService $auth): JsonResponse
    {
        $dto = RegisterDTO::fromArray($request->validated());

        $auth->register(
            name: $dto->name,
            email: $dto->email,
            password: $dto->password,
            ip: $request->ip(),
            ua: $request->userAgent(),
        );

        $user = User::query()->where('email', $dto->email)->firstOrFail();

        return (new UserResource($user))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request, AuthService $auth): JsonResponse
    {
        $dto = LoginDTO::fromArray($request->validated());
        $authTokensDTO = $auth->login($dto->email, $dto->password, $request->ip(), $request->userAgent());

        return $authTokensDTO->toResponse();
    }

    public function refresh(Request $request, AuthService $auth): JsonResponse
    {
        $data = $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        $tokensDTO = $auth->refresh($data['refresh_token'], $request->ip(), $request->userAgent());

        return $tokensDTO->toResponse();
    }

    public function logout(Request $request, AuthService $auth): JsonResponse
    {
        $data = $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        $auth->logout($data['refresh_token'], $request->ip(), $request->userAgent());

        return response()->json(['ok' => true]);
    }
}
