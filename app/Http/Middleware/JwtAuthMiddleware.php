<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Jwt\JwtVerifier;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

readonly class JwtAuthMiddleware
{
    public function __construct(private JwtVerifier $verifier) {}

    public function handle(Request $request, Closure $next)
    {
        $auth = $request->header('Authorization');

        if (! Str::of($auth)->startsWith('Bearer ')) {
            return response()->json(['error' => 'Missing bearer token'], 401);
        }

        $jwt = trim(substr($auth, 7));

        try {
            $token = $this->verifier->parseAndValidate($jwt);
        } catch (\Throwable $exception) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $sub = $token->claims()->get('sub', '');

        if ($sub === '') {
            return response()->json(['error' => 'Invalid token subject'], 401);
        }

        $user = User::query()->find($sub);

        if (! $user) {
            return response()->json(['error' => 'User not found'], 401);
        }

        auth()->setUser($user);

        return $next($request);
    }
}
