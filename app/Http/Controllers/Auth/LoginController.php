<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt([
            'samaccountname' => $credentials['login'],
            'password' => $credentials['password']
        ])) {
            return response()->json(['message' => 'Пользователь не найден'], 401);
        }

        $token = $request->user()->createToken('auth_token')->plainTextToken;

        return response()->json([
            'auth_token' => $token,
        ]);
    }
}
