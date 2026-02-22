<?php

namespace App\Services\Jwt;

use App\Support\Base64Url;

final class JwksService
{
    public function __construct(private readonly JwtKeys $keys) {}

    public function jwks(): array
    {
        $pub = openssl_pkey_get_public($this->keys->publicKeyPem);

        if ($pub === false) {
            throw new \RuntimeException('Unable to get public key');
        }

        $details = openssl_pkey_get_details($pub);
        if (! isset($details['rsa']['n'], $details['rsa']['e'])) {
            throw new \RuntimeException('Public key is not RSA');
        }

        return [
            'keys' => [[
                'kty' => 'RSA',
                'use' => 'sig',
                'alg' => 'RS256',
                'kid' => $this->keys->kid,
                'n' => Base64Url::encode($details['rsa']['n']),
                'e' => Base64Url::encode($details['rsa']['e']),
            ]],
        ];
    }
}
