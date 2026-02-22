<?php

return [
    'issuer' => env('JWT_ISSUER', 'http://localhost:8000'),
    'audiences' => array_values(array_filter(array_map('trim', explode(',', env('JWT_AUDIENCES', env('JWT_AUDIENCE', 'auth-gateway-service')))))),
    'access_ttl' => (int) env('JWT_ACCESS_TTL_SECONDS', 900),
    'refresh_ttl' => (int) env('JWT_REFRESH_TTL_SECONDS', 2592000),

    'kid' => env('JWT_KEY_ID', 'default'),

    // Use storage_path() so Windows paths are always correct
    'private_key_path' => env('JWT_PRIVATE_KEY_PATH')
        ? (str_starts_with(env('JWT_PRIVATE_KEY_PATH'), 'storage/')
            ? base_path(env('JWT_PRIVATE_KEY_PATH'))
            : env('JWT_PRIVATE_KEY_PATH'))
        : storage_path('jwt/private.pem'),

    'public_key_path' => env('JWT_PUBLIC_KEY_PATH')
        ? (str_starts_with(env('JWT_PUBLIC_KEY_PATH'), 'storage/')
            ? base_path(env('JWT_PUBLIC_KEY_PATH'))
            : env('JWT_PUBLIC_KEY_PATH'))
        : storage_path('jwt/public.pem'),
];
