<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if (!$token) {
            return response()->json(['message' => 'Token is missing or expired'], 401);
        }

        $token->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
