<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'senha' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->senha, $user->senha)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        // Retornar um token ou outra identificação (opcional)
        return response()->json([
            'message' => 'Login bem-sucedido',
            'user' => $user,
        ]);
    }
}

