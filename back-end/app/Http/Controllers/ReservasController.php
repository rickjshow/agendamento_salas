<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Ambientes;
use App\Models\Historico_reserva;

class ReservasController extends Controller
{
    // Listar reservas
    public function index(Request $request)
    {
        $usuarioId = $request->query('usuario_id');

        if ($usuarioId) {
            $usuario = User::find($usuarioId);

            if (!$usuario) {
                return response()->json(['error' => 'Usuário não encontrado.'], 404);
            }

            if ($usuario->papel === 'admin') {
                $reservas = Reserva::with('ambiente', 'usuario')
                    ->where('status', 'ativa')
                    ->get();
            } else {
                $reservas = Reserva::with('ambiente', 'usuario')
                    ->where('usuario_id', $usuario->id)
                    ->where('status', 'ativa')
                    ->get();
            }
        } else {
            $reservas = Reserva::with('ambiente', 'usuario')
                ->where('status', 'ativa')
                ->get();
        }

        return response()->json($reservas);
    }

    // Criar reserva
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ambiente_id' => 'required|exists:ambientes,id',
            'hora_inicio' => 'required|date|before:hora_fim',
            'hora_fim' => 'required|date|after:hora_inicio',
            'usuario_id' => 'required|exists:users,id',
            'status' => 'required|string|in:ativa,cancelada',
        ]);

        // Busca o responsável pela reserva
        $usuarioResponsavel = User::find($validated['usuario_id']);
        if (!$usuarioResponsavel) {
            return response()->json(['error' => 'Usuário responsável não encontrado.'], 404);
        }

        // Busca o ambiente
        $ambiente = Ambientes::find($validated['ambiente_id']);
        if (!$ambiente) {
            return response()->json(['error' => 'Ambiente não encontrado.'], 404);
        }

        // Verifica se há conflito de horário no mesmo ambiente
        $conflito = Reserva::where('ambiente_id', $validated['ambiente_id'])
            ->where(function ($query) use ($validated) {
                $query->where('hora_inicio', '<', $validated['hora_fim'])
                    ->where('hora_fim', '>', $validated['hora_inicio']);
            })
            ->exists();

        if ($conflito) {
            return response()->json(['error' => 'Conflito de horário! Já existe uma reserva para esse ambiente no período selecionado.'], 409);
        }

        // Cria a reserva
        $reserva = Reserva::create([
            'ambiente_id' => $validated['ambiente_id'],
            'hora_inicio' => $validated['hora_inicio'],
            'hora_fim' => $validated['hora_fim'],
            'usuario_id' => $validated['usuario_id'],
            'status' => $validated['status'],
        ]);

        // Registro no histórico
        Historico_reserva::create([
            'reserva_id' => $reserva->id,
            'nome_usuario_responsavel' => $usuarioResponsavel->nome,
            'nome_usuario_alteracao' => $usuarioResponsavel->nome,
            'nome_ambiente' => $ambiente->nome ?? 'Ambiente não encontrado',
            'hora_inicio' => $reserva->hora_inicio,
            'hora_fim' => $reserva->hora_fim,
            'alteracoes' => 'Reserva criada.',
            'modificado_em' => now(),
        ]);

        return response()->json($reserva, 201);
    }

    public function update(Request $request, $id)
    {
        // Validação da entrada
        $validated = $request->validate([
            'ambiente_id' => 'required|exists:ambientes,id',
            'hora_inicio' => 'required|date',
            'hora_fim' => 'required|date|after:hora_inicio',
            'usuario_id' => 'required|exists:users,id', // ID do usuário que realizou a alteração
        ]);

        // Busca a reserva pelo ID
        $reserva = Reserva::find($id);
        if (!$reserva) {
            return response()->json(['error' => 'Reserva não encontrada.'], 404);
        }

        // Busca o usuário responsável pela reserva
        $usuarioResponsavel = User::find($reserva->usuario_id);
        if (!$usuarioResponsavel) {
            return response()->json(['error' => 'Usuário responsável não encontrado.'], 404);
        }

        // Busca o usuário que realizou a alteração
        $usuarioAlteracao = User::find($validated['usuario_id']);
        if (!$usuarioAlteracao) {
            return response()->json(['error' => 'Usuário de alteração não encontrado.'], 404);
        }

        // Busca o ambiente
        $ambiente = Ambientes::find($validated['ambiente_id']);
        if (!$ambiente) {
            return response()->json(['error' => 'Ambiente não encontrado.'], 404);
        }

        // Verifica e registra as alterações
        $alteracoes = [];
        if ($reserva->ambiente_id != $validated['ambiente_id']) {
            $alteracoes[] = "Ambiente alterado.";
        }
        if ($reserva->hora_inicio != $validated['hora_inicio']) {
            $alteracoes[] = "Horário de início alterado.";
        }
        if ($reserva->hora_fim != $validated['hora_fim']) {
            $alteracoes[] = "Horário de fim alterado.";
        }

        // Registra no histórico
        Historico_reserva::create([
            'reserva_id' => $reserva->id,
            'nome_usuario_responsavel' => $usuarioResponsavel->nome, // Nome do responsável original
            'nome_usuario_alteracao' => $usuarioAlteracao->nome,     // Nome do usuário que realizou a alteração
            'nome_ambiente' => $ambiente->nome,
            'hora_inicio' => $validated['hora_inicio'],
            'hora_fim' => $validated['hora_fim'],
            'alteracoes' => implode(' ', $alteracoes) ?: 'Nenhuma alteração registrada.',
            'modificado_em' => now(),
        ]);

        // Atualiza a reserva
        $reserva->update([
            'ambiente_id' => $validated['ambiente_id'],
            'hora_inicio' => $validated['hora_inicio'],
            'hora_fim' => $validated['hora_fim'],
        ]);

        return response()->json($reserva);
    }

    // Excluir reserva
    public function destroy(Request $request, $id)
    {
        // Validação da entrada
        $validated = $request->validate([
            'usuario_id' => 'required|exists:users,id', // ID do usuário que realizou a exclusão
        ]);

        // Busca a reserva pelo ID
        $reserva = Reserva::find($id);
        if (!$reserva) {
            return response()->json(['error' => 'Reserva não encontrada.'], 404);
        }

        // Busca o responsável pela reserva
        $usuarioResponsavel = User::find($reserva->usuario_id);
        if (!$usuarioResponsavel) {
            return response()->json(['error' => 'Usuário responsável não encontrado.'], 404);
        }

        // Busca o usuário que realizou a exclusão
        $usuarioAlteracao = User::find($validated['usuario_id']);
        if (!$usuarioAlteracao) {
            return response()->json(['error' => 'Usuário de alteração não encontrado.'], 404);
        }

        // Registra no histórico
        Historico_reserva::create([
            'reserva_id' => $reserva->id,
            'nome_usuario_responsavel' => $usuarioResponsavel->nome, // Nome do responsável original
            'nome_usuario_alteracao' => $usuarioAlteracao->nome,     // Nome do usuário que realizou a exclusão
            'nome_ambiente' => $reserva->ambiente->nome ?? 'Ambiente não encontrado',
            'hora_inicio' => $reserva->hora_inicio,
            'hora_fim' => $reserva->hora_fim,
            'alteracoes' => 'Reserva excluída.',
            'modificado_em' => now(),
        ]);

        // Exclui a reserva
        $reserva->delete();

        return response()->json(['message' => 'Reserva excluída com sucesso e registrada no histórico.']);
    }
}
