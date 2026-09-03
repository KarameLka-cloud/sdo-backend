<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $authenticated = Auth::attempt([
            'samaccountname' => $credentials['login'],
            'password' => $credentials['password'],
        ]);

        if (! $authenticated) {
            return response()->json(['message' => 'Неверный логин или пароль'], 401);
        }

        $user = $request->user()->loadMissing('roles');

        // One active token per user: otherwise every sign-in leaves behind
        // another valid credential until it expires on its own.
        $user->tokens()->delete();

        return response()->json([
            'auth_token' => $user->createToken('auth_token')->plainTextToken,
            'user' => $user,
        ]);
    }
}
