<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Ambientes;
use App\Models\Historico_reserva;
use App\Models\Notificacoes; // Adicione essa linha para importar a model de notificações


class ReservasController extends Controller
{
    // Listar reservas
    public function index()
    {
        // Busca todas as reservas ativas
        $reservas = Reserva::with('ambiente', 'usuario')
            ->where('status', 'ativa')
            ->get();

        return response()->json($reservas);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'ambiente_id' => 'required|exists:ambientes,id',
            'hora_inicio' => 'required|date|before:hora_fim',
            'hora_fim' => 'required|date|after:hora_inicio',
            'usuario_id' => 'required|exists:users,id',
            'status' => 'required|string|in:ativa,cancelada',
        ]);

        $horaAtual = now();
        $horaInicio = new \DateTime($validated['hora_inicio']);

        // Validação adicional: verificar se a hora inicial é no passado
        if ($horaInicio < $horaAtual) {
            return response()->json(['error' => 'Não é permitido criar reservas para horários inferiores ao horário atual.'], 422);
        }

        $usuarioId = $validated['usuario_id'];
        $ambienteId = $validated['ambiente_id'];
        $horaInicio = $validated['hora_inicio'];
        $horaFim = $validated['hora_fim'];

        // Verificar conflito de horário no mesmo ambiente
        $conflito = Reserva::where('ambiente_id', $ambienteId)
            ->where('status', 'ativa')
            ->where(function ($query) use ($horaInicio, $horaFim) {
                $query->where(function ($subQuery) use ($horaInicio, $horaFim) {
                    $subQuery->where('hora_inicio', '<', $horaFim)
                             ->where('hora_fim', '>', $horaInicio);
                });
            })
            ->exists();

        if ($conflito) {
            return response()->json(['error' => 'Conflito de horário! Já existe uma reserva para este ambiente no período selecionado.'], 409);
        }

        // Verificar se o usuário já tem uma reserva no mesmo dia
        $dia = date('Y-m-d', strtotime($horaInicio));
        $reservasNoDia = Reserva::where('usuario_id', $usuarioId)
            ->whereDate('hora_inicio', $dia)
            ->where('status', 'ativa')
            ->count();

        if ($reservasNoDia >= 1) {
            return response()->json(['error' => 'O usuário já possui uma reserva ativa neste dia.'], 409);
        }

        // Verificar se o usuário já tem mais de duas reservas na semana
        $inicioDaSemana = date('Y-m-d', strtotime('monday this week', strtotime($dia)));
        $fimDaSemana = date('Y-m-d', strtotime('sunday this week', strtotime($dia)));

        $reservasNaSemana = Reserva::where('usuario_id', $usuarioId)
            ->whereBetween('hora_inicio', [$inicioDaSemana, $fimDaSemana])
            ->where('status', 'ativa')
            ->count();

        if ($reservasNaSemana >= 2) {
            return response()->json(['error' => 'O usuário já possui duas reservas ativas nesta semana.'], 409);
        }

        // Cria a reserva
        $reserva = Reserva::create($validated);

        // Registro no histórico
        Historico_reserva::create([
            'reserva_id' => $reserva->id,
            'nome_usuario_responsavel' => User::find($usuarioId)->nome,
            'nome_usuario_alteracao' => User::find($usuarioId)->nome,
            'nome_ambiente' => Ambientes::find($ambienteId)->nome ?? 'Ambiente não encontrado',
            'hora_inicio' => $horaInicio,
            'hora_fim' => $horaFim,
            'alteracoes' => 'Reserva criada.',
            'modificado_em' => now(),
        ]);

        $data = date('Y-m-d', strtotime($horaInicio));
        $horaInicioFormatada = date('H:i', strtotime($horaInicio));
        $horaFimFormatada = date('H:i', strtotime($horaFim));

        Notificacoes::create([
            'usuario_id' => $usuarioId,
            'mensagem' => "A reserva para o ambiente " . Ambientes::find($ambienteId)->nome .
                          " no dia $data, das $horaInicioFormatada até $horaFimFormatada foi aprovada.",
            'tipo' => 'reserva',
            'criado_em' => now(),
        ]);

        return response()->json($reserva, 201);
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'ambiente_id' => 'required|exists:ambientes,id',
            'hora_inicio' => 'required|date',
            'hora_fim' => 'required|date|after:hora_inicio',
            'usuario_id' => 'required|exists:users,id',
        ]);

        $reserva = Reserva::find($id);
        if (!$reserva) {
            return response()->json(['error' => 'Reserva não encontrada.'], 404);
        }

        $horaAtual = now();
        $horaInicio = new \DateTime($validated['hora_inicio']);

        // Validação adicional: verificar se a hora inicial é no passado
        if ($horaInicio < $horaAtual) {
            return response()->json(['error' => 'Não é permitido atualizar reservas para horários inferiores ao horário atual.'], 422);
        }

        $usuarioId = $validated['usuario_id'];
        $ambienteId = $validated['ambiente_id'];
        $horaInicio = $validated['hora_inicio'];
        $horaFim = $validated['hora_fim'];

        // Verificar conflito de horário no mesmo ambiente
        $conflito = Reserva::where('ambiente_id', $ambienteId)
            ->where('status', 'ativa')
            ->where('id', '!=', $id)
            ->where(function ($query) use ($horaInicio, $horaFim) {
                $query->where(function ($subQuery) use ($horaInicio, $horaFim) {
                    $subQuery->where('hora_inicio', '<', $horaFim)
                            ->where('hora_fim', '>', $horaInicio);
                });
            })
            ->exists();

        if ($conflito) {
            return response()->json(['error' => 'Conflito de horário! Já existe uma reserva para este ambiente no período selecionado.'], 409);
        }

        // Verificar se outra reserva já existe no mesmo dia
        $dia = date('Y-m-d', strtotime($horaInicio));
        $reservasNoDia = Reserva::where('usuario_id', $usuarioId)
            ->whereDate('hora_inicio', $dia)
            ->where('id', '!=', $id)
            ->where('status', 'ativa')
            ->count();

        if ($reservasNoDia >= 1) {
            return response()->json(['error' => 'O usuário já possui uma reserva ativa neste dia.'], 409);
        }

        // Verificar se mais de duas reservas na semana já existem
        $inicioDaSemana = date('Y-m-d', strtotime('monday this week', strtotime($dia)));
        $fimDaSemana = date('Y-m-d', strtotime('sunday this week', strtotime($dia)));

        $reservasNaSemana = Reserva::where('usuario_id', $usuarioId)
            ->whereBetween('hora_inicio', [$inicioDaSemana, $fimDaSemana])
            ->where('id', '!=', $id)
            ->where('status', 'ativa')
            ->count();

        if ($reservasNaSemana >= 2) {
            return response()->json(['error' => 'O usuário já possui duas reservas ativas nesta semana.'], 409);
        }

        // Atualiza a reserva
        $alteracoes = [];
        if ($reserva->ambiente_id != $ambienteId) {
            $alteracoes[] = "Ambiente alterado.";
        }
        if ($reserva->hora_inicio != $horaInicio) {
            $alteracoes[] = "Horário de início alterado.";
        }
        if ($reserva->hora_fim != $horaFim) {
            $alteracoes[] = "Horário de fim alterado.";
        }

        $reserva->update($validated);

        Historico_reserva::create([
            'reserva_id' => $reserva->id,
            'nome_usuario_responsavel' => User::find($reserva->usuario_id)->nome,
            'nome_usuario_alteracao' => User::find($usuarioId)->nome,
            'nome_ambiente' => Ambientes::find($ambienteId)->nome,
            'hora_inicio' => $horaInicio,
            'hora_fim' => $horaFim,
            'alteracoes' => implode(' ', $alteracoes) ?: 'Nenhuma alteração registrada.',
            'modificado_em' => now(),
        ]);

        return response()->json($reserva);
    }



    // Excluir reserva
    public function destroy(Request $request, $id)
    {
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

        // Valida se o usuário que está excluindo é professor e se está com antecedência de 24 horas
        if ($usuarioAlteracao->papel === 'professor') {
            $horaAtual = now();
            $horaInicioReserva = new \DateTime($reserva->hora_inicio);
            $diferenca = $horaAtual->diff($horaInicioReserva);

            // Verifica se a diferença é menor que 24 horas
            if ($horaAtual > $horaInicioReserva || $diferenca->h < 24) {
                return response()->json([
                    'error' => 'Professores só podem excluir reservas com 24 horas de antecedência.',
                ], 403);
            }
        }

        // Registra no histórico
        Historico_reserva::create([
            'reserva_id' => $reserva->id,
            'nome_usuario_responsavel' => $usuarioResponsavel->nome,
            'nome_usuario_alteracao' => $usuarioAlteracao->nome,
            'nome_ambiente' => $reserva->ambiente->nome ?? 'Ambiente não encontrado',
            'hora_inicio' => $reserva->hora_inicio,
            'hora_fim' => $reserva->hora_fim,
            'alteracoes' => 'Reserva excluída.',
            'modificado_em' => now(),
        ]);

        // Adicionar notificação
        $data = date('Y-m-d', strtotime($reserva->hora_inicio));
        $horaInicioFormatada = date('H:i', strtotime($reserva->hora_inicio));
        $horaFimFormatada = date('H:i', strtotime($reserva->hora_fim));

        Notificacoes::create([
            'usuario_id' => $reserva->usuario_id,
            'mensagem' => "A reserva para o ambiente " . $reserva->ambiente->nome .
                        " no dia $data, das $horaInicioFormatada até $horaFimFormatada foi cancelada.",
            'tipo' => 'cancelamento',
            'criado_em' => now(),
        ]);

        // Exclui a reserva
        $reserva->delete();

        return response()->json(['message' => 'Reserva excluída com sucesso e registrada no histórico.']);
    }

}
