<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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

        if (!$user) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        // Verificar se o usuário está inativo
        if ($user->status !== 'ativo') {
            return response()->json(['message' => 'Seu usuário está inativo. Entre em contato com o administrador.'], 403);
        }

        // Verificar as credenciais
        if (!Hash::check($request->senha, $user->senha)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        // Retorna o usuário logado com o ID explicitamente
        return response()->json([
            'message' => 'Login bem-sucedido',
            'user' => [
                'id' => $user->id,
                'nome' => $user->nome,
                'email' => $user->email,
                'papel' => $user->papel,
                'senha_resetada' => $user->senha_resetada,
                'status' => $user->status,
            ],
        ]);
    }


    public function alterarSenha(Request $request, $id)
    {
        // Validação dos dados enviados
        $validator = Validator::make($request->all(), [
            'senha' => 'required|string|min:6|confirmed', // 'confirmed' exige o campo 'senha_confirmation'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro na validação dos dados.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Buscar o usuário pelo ID
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json([
                'message' => 'Usuário não encontrado.',
            ], 404);
        }

        // Verificar se a nova senha é igual à senha atual
        if (Hash::check($request->senha, $usuario->senha)) {
            return response()->json([
                'message' => 'A nova senha não pode ser igual à senha atual.',
            ], 422);
        }

        // Atualizar a senha do usuário
        $usuario->senha = Hash::make($request->senha);
        $usuario->senha_resetada = 'não'; // Indica que a senha foi redefinida
        $usuario->save();

        return response()->json([
            'message' => 'Senha alterada com sucesso!',
        ], 200);
    }

}

