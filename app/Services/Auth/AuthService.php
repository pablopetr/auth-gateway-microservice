<?php

namespace App\Services\Auth;

use App\DTO\Auth\AuthTokensDTO;
use App\Models\AuditLog;
use App\Models\RefreshToken;
use App\Models\User;
use App\Services\Jwt\JwtIssuer;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AuthService
{
    public function __construct(private readonly JwtIssuer $issuer) {}

    public function register(string $name, string $email, string $password, ?string $ip, ?string $ua): AuthTokensDTO
    {
        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        event(new Registered($user));

        $this->audit($user->id, 'auth.register', $ip, $ua);

        return $this->issueTokensFor($user, $ip, $ua);
    }

    public function login(string $email, string $password, ?string $ip, ?string $ua): AuthTokensDTO
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            $this->audit($user->id, 'auth.login.failed', $ip, $ua, ['email' => $email]);

            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }

        $this->audit($user->id, 'auth.login.success', $ip, $ua);

        return $this->issueTokensFor($user, $ip, $ua);
    }

    public function refresh(string $refreshToken, ?string $ip, ?string $ua): AuthTokensDTO
    {
        [$tokenId, $secret] = $this->splitRefreshToken($refreshToken);

        /** @var RefreshToken|null $stored */
        $stored = RefreshToken::query()->where('token_id', $tokenId)->first();

        if (! $stored) {
            $this->audit(null, 'auth.refresh.failed', $ip, $ua, ['reason' => 'not_found']);
            throw ValidationException::withMessages(['refresh_token' => ['Invalid refresh token.']]);
        }

        if ($stored->isRevoked() || $stored->isExpired()) {
            $this->audit($stored->user_id, 'auth.refresh.failed', $ip, $ua, ['reason' => 'revoked_or_expired']);
            throw ValidationException::withMessages(['refresh_token' => ['Refresh token expired or revoked.']]);
        }

        if (! hash_equals($stored->token_hash, hash('sha256', $secret))) {
            $this->audit($stored->user_id, 'auth.refresh.failed', $ip, $ua, ['reason' => 'hash_mismatch']);
            throw ValidationException::withMessages(['refresh_token' => ['Invalid refresh token.']]);
        }

        $user = User::query()->findOrFail($stored->user_id);

        $newTokenId = (string) Str::uuid();
        $stored->update([
            'revoked_at' => now(),
            'replaced_by_token_id' => $newTokenId,
        ]);

        $this->audit($user->id, 'auth.refresh.success', $ip, $ua);

        return $this->issueTokensFor($user, $ip, $ua, $newTokenId);
    }

    public function logout(string $refreshToken, ?string $ip, ?string $ua): void
    {
        [$tokenId, $secret] = $this->splitRefreshToken($refreshToken);

        $stored = RefreshToken::query()->where('token_id', $tokenId)->first();
        if (! $stored) {
            $this->audit(null, 'auth.logout.ignored', $ip, $ua, ['reason' => 'not_found']);

            return;
        }

        if (! hash_equals($stored->token_hash, hash('sha256', $secret))) {
            $this->audit($stored->user_id, 'auth.logout.failed', $ip, $ua, ['reason' => 'hash_mismatch']);

            return;
        }

        $stored->update(['revoked_at' => now()]);
        $this->audit($stored->user_id, 'auth.logout.success', $ip, $ua);
    }

    private function issueTokensFor(User $user, ?string $ip, ?string $ua, ?string $forcedRefreshTokenId = null): AuthTokensDTO
    {
        $access = $this->issuer->issueAccessToken($user);

        $refreshTtl = (int) config('auth_gateway.refresh_ttl');
        $expiresAt = CarbonImmutable::now()->addSeconds($refreshTtl);

        $tokenId = $forcedRefreshTokenId ?: (string) Str::uuid();
        $secret = Str::random(80);
        $hash = hash('sha256', $secret);

        RefreshToken::query()->create([
            'user_id' => $user->id,
            'token_id' => $tokenId,
            'token_hash' => $hash,
            'expires_at' => $expiresAt,
            'ip' => $ip,
            'user_agent' => $ua,
        ]);

        $refreshToken = $tokenId.'.'.$secret;

        return new AuthTokensDTO(
            accessToken: $access['token'],
            accessTokenExpiresIn: $access['expires_in'],
            refreshToken: $refreshToken,
            refreshTokenExpiresIn: $refreshTtl,
        );
    }

    private function splitRefreshToken(string $token): array
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw ValidationException::withMessages(['refresh_token' => ['Invalid refresh token format.']]);
        }

        return [$parts[0], $parts[1]];
    }

    private function audit(?int $userId, string $event, ?string $ip, ?string $ua, array $ctx = []): void
    {
        AuditLog::query()->create([
            'user_id' => $userId,
            'event' => $event,
            'ip' => $ip,
            'user_agent' => $ua,
            'context' => $ctx ?: null,
        ]);
    }
}
