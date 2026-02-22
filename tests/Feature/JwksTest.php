<?php

it('exposes jwks', function () {
    $this->getJson('/api/.well-known/jwks.json')
        ->assertOk()
        ->assertJsonStructure([
            'keys' => [[
                'kty', 'use', 'alg', 'kid', 'n', 'e',
            ]],
        ]);
});
