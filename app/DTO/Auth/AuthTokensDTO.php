<?php

namespace App\DTO\Auth;

use Illuminate\Http\JsonResponse;

final class AuthTokensDTO
{
    public function __construct(
        public string $accessToken,
        public int $accessTokenExpiresIn,
        public string $refreshToken,
        public int $refreshTokenExpiresIn,
        public string $tokenType = 'Bearer',
    ) {}

    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->accessTokenExpiresIn,
            'refresh_token' => $this->refreshToken,
            'refresh_token_expires_in' => $this->refreshTokenExpiresIn,
        ];
    }

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->accessTokenExpiresIn,
            'refresh_token' => $this->refreshToken,
            'refresh_token_expires_in' => $this->refreshTokenExpiresIn,
        ]);
    }
}
