<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GerenciamentoUsuariosController extends Controller
{
    public function index()
    {
        $usuarios = User::all();
        return response()->json($usuarios);
    }

    /**
     * Criar um novo usuário.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'papel' => 'required|string|in:admin,professor',
        ]);

        // Define a senha padrão com base no papel
        $senhaPadrao = $request->papel === 'admin' ? 'admin123' : 'professor123';

        $usuario = User::create([
            'nome' => $request->nome,
            'email' => $request->email,
            'senha' => Hash::make($senhaPadrao),
            'papel' => $request->papel,
            'senha_resetada' => 'sim',
            'status' => $request->status ?? 'ativo',
        ]);

        return response()->json(['mensagem' => 'Usuário criado com sucesso!', 'usuario' => $usuario], 201);
    }

    public function resetPassword($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['mensagem' => 'Usuário não encontrado.'], 404);
        }

        // Define a senha padrão com base no papel
        $senhaPadrao = $usuario->papel === 'admin' ? 'admin123' : 'professor123';

        $usuario->update([
            'senha' => Hash::make($senhaPadrao),
            'senha_resetada' => 'sim',
        ]);

        return response()->json(['mensagem' => 'Senha resetada com sucesso!', 'usuario' => $usuario]);
    }



    /**
     * Atualizar um usuário existente.
     */
    public function update(Request $request, $id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['mensagem' => 'Usuário não encontrado.'], 404);
        }

        $request->validate([
            'nome' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'senha' => 'sometimes|string|min:6',
            'papel' => 'sometimes|string|in:admin,professor',
            'status' => 'sometimes|string|in:ativo,inativo',
        ]);

        $usuario->update([
            'nome' => $request->nome ?? $usuario->nome,
            'email' => $request->email ?? $usuario->email,
            'senha' => $request->senha ? Hash::make($request->senha) : $usuario->senha,
            'papel' => $request->papel ?? $usuario->papel,
            'status' => $request->status ?? $usuario->status,
        ]);

        return response()->json(['mensagem' => 'Usuário atualizado com sucesso!', 'usuario' => $usuario]);
    }

    /**
     * Excluir um usuário.
     */
    public function destroy($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['mensagem' => 'Usuário não encontrado.'], 404);
        }

        $usuario->delete();

        return response()->json(['mensagem' => 'Usuário excluído com sucesso.']);
    }
}
