<?php

namespace App\Services\Jwt;

use App\Models\User;
use DateInterval;
use Illuminate\Support\Str;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;

final class JwtIssuer
{
    private Configuration $config;

    public function __construct(JwtKeys $keys)
    {
        $this->config = Configuration::forAsymmetricSigner(
            new Sha256,
            InMemory::plainText($keys->privateKeyPem),
            InMemory::plainText($keys->publicKeyPem)
        );

        $this->config->withValidationConstraints();
    }

    /** @return array{token:string, expires_in:int, jti:string} */
    public function issueAccessToken(User $user, ?string $audience = null): array
    {
        $now = new \DateTimeImmutable;

        $ttl = (int) config('auth_gateway.access_ttl', 900);
        $issuer = (string) config('auth_gateway.issuer');

        $audiences = (array) config('auth_gateway.audiences', []);
        $aud = $audience ?: ($audiences[0] ?? null);

        if (! $aud) {
            throw new \RuntimeException('JWT audience not configured. Set JWT_AUDIENCES or JWT_AUDIENCE in .env');
        }

        $jti = (string) Str::uuid();

        $token = $this->config->builder()
            ->issuedBy($issuer)
            ->permittedFor($aud)
            ->identifiedBy($jti)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->add(new DateInterval('PT'.$ttl.'S')))
            ->relatedTo((string) $user->id)
            ->withClaim('email', $user->email)
            ->getToken($this->config->signer(), $this->config->signingKey())
            ->toString();

        return [
            'token' => $token,
            'expires_in' => $ttl,
            'jti' => $jti,
        ];
    }
}
