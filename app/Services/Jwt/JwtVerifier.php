<?php

namespace App\Services\Jwt;

use App\Support\Clock\UtcClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;

final class JwtVerifier
{
    private Configuration $config;

    public function __construct(JwtKeys $keys)
    {
        $this->config = Configuration::forAsymmetricSigner(
            new Sha256,
            InMemory::plainText($keys->privateKeyPem),
            InMemory::plainText($keys->publicKeyPem),
        );
    }

    /** @throws \Throwable */
    public function parseAndValidate(string $jwt): Token
    {
        $token = $this->config->parser()->parse($jwt);

        $issuer = (string) config('auth_gateway.issuer');
        $allowedAudiences = (array) config('auth_gateway.audiences', []);

        // 1) Signature + issuer + time validation
        $this->config->validator()->assert(
            $token,
            new SignedWith($this->config->signer(), $this->config->verificationKey()),
            new IssuedBy($issuer),
            new StrictValidAt(new UtcClock),
        );

        // 2) Audience validation (manual, OR logic)
        $audClaim = $token->claims()->get('aud', []);
        $tokenAudiences = is_array($audClaim) ? $audClaim : [$audClaim];

        if (empty($allowedAudiences)) {
            throw new \RuntimeException('No allowed audiences configured (JWT_AUDIENCES/JWT_AUDIENCE).');
        }

        $ok = count(array_intersect($allowedAudiences, $tokenAudiences)) > 0;

        if (! $ok) {
            throw new \RuntimeException('Invalid audience.');
        }

        return $token;
    }
}
