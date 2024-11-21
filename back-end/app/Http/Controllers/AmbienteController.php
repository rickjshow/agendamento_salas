<?php

namespace App\Http\Controllers;

use App\Models\Ambientes;
use Illuminate\Http\Request;

class AmbienteController extends Controller
{
    // Listar todos os ambientes
    public function index()
    {
        $ambientes = Ambientes::all();
        return response()->json($ambientes);
    }

    // Criar novo ambiente
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            'status' => 'required|in:Disponivel,Reservado,Manutencao',
            'descricao' => 'nullable|string',
        ]);
    
        $ambiente = Ambientes::create($request->all());
        return response()->json($ambiente, 201);
    }

    // Mostrar ambiente específico
    public function show($id)
    {
        $ambiente = Ambientes::find($id);

        if (!$ambiente) {
            return response()->json(['message' => 'Ambiente não encontrado'], 404);
        }

        return response()->json($ambiente);
    }

    // Atualizar ambiente
    public function update(Request $request, $id)
    {
        $ambiente = Ambientes::find($id);
    
        if (!$ambiente) {
            return response()->json(['message' => 'Ambiente não encontrado'], 404);
        }
    
        $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            'status' => 'required|in:Disponivel,Reservado,Manutencao',
            'descricao' => 'nullable|string',
        ]);
    
        $ambiente->update($request->all());
        return response()->json($ambiente);
    }
    

    // Deletar ambiente
    public function destroy($id)
    {
        $ambiente = Ambientes::find($id);

        if (!$ambiente) {
            return response()->json(['message' => 'Ambiente não encontrado'], 404);
        }

        $ambiente->delete();
        return response()->json(['message' => 'Ambiente excluído com sucesso']);
    }
}
