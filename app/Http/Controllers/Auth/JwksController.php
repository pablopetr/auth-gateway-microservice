<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Jwt\JwksService;

class JwksController extends Controller
{
    public function __invoke(JwksService $jwks): \Illuminate\Http\JsonResponse
    {
        return response()->json($jwks->jwks());
    }
}
