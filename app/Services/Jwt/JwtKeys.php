<?php

namespace App\Services\Jwt;

final class JwtKeys
{
    public function __construct(
        public string $kid,
        public string $privateKeyPem,
        public string $publicKeyPem,
    ) {}

    public static function loadFromConfig(): self
    {
        $kid = (string) config('auth_gateway.kid');
        $privPath = (string) config('auth_gateway.private_key_path');
        $pubPath = (string) config('auth_gateway.public_key_path');

        $priv = @file_get_contents($privPath);
        $pub = @file_get_contents($pubPath);

        if (! $priv || ! $pub) {
            throw new \RuntimeException('JWT keys not found. Check JWT_*_KEY_PATH in .env');
        }

        return new self($kid, $priv, $pub);
    }
}
