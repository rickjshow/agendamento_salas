<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Historico_reserva;
use App\Models\User;

class Historico_reservasController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $usuarioId = $request->query('usuario_id');

        if ($usuarioId) {
            // Filtra os históricos pelo usuário (professor)
            $historicoReservas = Historico_reserva::whereHas('reserva', function ($query) use ($usuarioId) {
                $query->where('usuario_id', $usuarioId);
            })->get();
        } else {
            // Retorna todos os históricos (admin)
            $historicoReservas = Historico_reserva::all();
        }

        // Formata os dados para retorno
        $response = $historicoReservas->map(function ($historico) {
            return [
                'id' => $historico->id,
                'reserva_id' => $historico->reserva_id,
                'alteracoes' => $historico->alteracoes,
                'modificado_em' => $historico->modificado_em,
                'nome_usuario_reservado' => $historico->nome_usuario_responsavel ?? 'Não especificado',
                'nome_usuario_alteracao' => $historico->nome_usuario_alteracao ?? 'Não especificado', // Confirma o envio
                'hora_inicio' => $historico->hora_inicio ?? 'Hora de início não encontrada',
                'hora_fim' => $historico->hora_fim ?? 'Hora de fim não encontrada',
            ];
        });

        return response()->json($response);
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
