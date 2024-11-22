<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notificacoes; // Certifique-se de que o modelo Notificacao está correto

class NotificacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        if (!$id) {
            return response()->json(['message' => 'ID do usuário é obrigatório.'], 400);
        }

        $notificacoes = Notificacoes::where('usuario_id', $id)->get();

        return response()->json($notificacoes);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
