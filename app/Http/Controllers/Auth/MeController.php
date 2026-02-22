<?php

namespace App\Http\Controllers\Auth;

use App\DTO\User\UserProfileDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'user' => UserProfileDTO::fromUser($request->user())->toArray(),
        ]);
    }
}
