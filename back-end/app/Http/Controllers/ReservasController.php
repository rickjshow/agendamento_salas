<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Ambientes;
use App\Models\Historico_reserva;
use Carbon\Carbon;

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

        // Verifica o status do ambiente
        $ambiente = Ambientes::find($validated['ambiente_id']);

        if (!$ambiente) {
            return response()->json(['error' => 'Ambiente não encontrado.'], 404);
        }

        if ($ambiente->status === 'Manutencao') {
            return response()->json(['error' => 'Este ambiente está em manutenção e não pode ser reservado.'], 403);
        }

        // Verifica conflito de horário
        $conflito = Reserva::where('ambiente_id', $validated['ambiente_id'])
            ->where(function ($query) use ($validated) {
                $query->where('hora_inicio', '<', $validated['hora_fim'])
                      ->where('hora_fim', '>', $validated['hora_inicio']);
            })
            ->exists();

        if ($conflito) {
            return response()->json(['error' => 'Esse horário já está reservado.'], 409);
        }

        // Verifica limite diário
        $reservaMesmoDia = Reserva::where('usuario_id', $validated['usuario_id'])
            ->whereDate('hora_inicio', '=', date('Y-m-d', strtotime($validated['hora_inicio'])))
            ->exists();

        if ($reservaMesmoDia) {
            return response()->json(['error' => 'Você já possui uma reserva neste dia.'], 403);
        }

        // Verifica limite semanal
        $dataInicio = Carbon::parse($validated['hora_inicio'])->startOfWeek();
        $dataFim = Carbon::parse($validated['hora_inicio'])->endOfWeek();

        $reservasSemana = Reserva::where('usuario_id', $validated['usuario_id'])
            ->whereBetween('hora_inicio', [$dataInicio, $dataFim])
            ->count();

        if ($reservasSemana >= 2) {
            return response()->json(['error' => 'Você já atingiu o limite de 2 reservas nesta semana.'], 403);
        }

        // Cria a reserva
        $reserva = Reserva::create($validated);

        // Registro no histórico
        Historico_reserva::create([
            'reserva_id' => $reserva->id,
            'alteracoes' => 'Reserva criada.',
            'modificado_em' => now(),
        ]);

        return response()->json($reserva, 201);
    }

    // Atualizar reserva
    public function update(Request $request, $id)
    {
        $reserva = Reserva::find($id);

        if (!$reserva) {
            return response()->json(['error' => 'Reserva não encontrada.'], 404);
        }

        $validated = $request->validate([
            'ambiente_id' => 'required|exists:ambientes,id',
            'hora_inicio' => 'required|date',
            'hora_fim' => 'required|date|after:hora_inicio',
        ]);

        $alteracoes = [];

        if ($reserva->ambiente_id != $validated['ambiente_id']) {
            $alteracoes[] = "Ambiente alterado de ID {$reserva->ambiente_id} para ID {$validated['ambiente_id']}.";
        }

        if ($reserva->hora_inicio != $validated['hora_inicio']) {
            $alteracoes[] = "Horário de início alterado de {$reserva->hora_inicio} para {$validated['hora_inicio']}.";
        }

        if ($reserva->hora_fim != $validated['hora_fim']) {
            $alteracoes[] = "Horário de fim alterado de {$reserva->hora_fim} para {$validated['hora_fim']}.";
        }

        if (!empty($alteracoes)) {
            Historico_reserva::create([
                'reserva_id' => $reserva->id,
                'alteracoes' => implode(' ', $alteracoes),
                'modificado_em' => now(),
            ]);
        }

        $reserva->update($validated);

        return response()->json($reserva);
    }

    // Excluir reserva
    public function destroy(Request $request, $id)
{
    $reserva = Reserva::find($id);

    if (!$reserva) {
        return response()->json(['error' => 'Reserva não encontrada.'], 404);
    }

    $usuarioId = $request->input('usuario_id');

    if ($usuarioId) {
        $usuario = User::find($usuarioId);

        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado.'], 404);
        }

        if ($usuario->papel === 'professor') {
            $horaAtual = Carbon::now();
            $horaReserva = Carbon::parse($reserva->hora_inicio);

            if ($horaAtual->diffInHours($horaReserva, false) < 24) {
                return response()->json(['error' => 'Professores só podem cancelar reservas com pelo menos 24 horas de antecedência.'], 403);
            }
        } elseif ($usuario->papel !== 'admin' && $reserva->usuario_id !== $usuarioId) {
            return response()->json(['error' => 'Você não tem permissão para excluir esta reserva.'], 403);
        }
    }

    // Registra o histórico antes de excluir
    Historico_reserva::create([
        'reserva_id' => $reserva->id,
        'alteracoes' => 'Reserva cancelada.',
        'modificado_em' => now(),
    ]);

    // Excluir a reserva
    $reserva->delete();

    return response()->json(['message' => 'Reserva excluída com sucesso e registrada no histórico.']);
}

    // Calcular horários disponíveis
    public function horariosDisponiveis($ambienteId)
    {
        $reservas = Reserva::where('ambiente_id', $ambienteId)->get();

        $horaAbertura = Carbon::createFromTime(8, 0);
        $horaFechamento = Carbon::createFromTime(18, 0);
        $horariosDisponiveis = [];
        $horaAtual = clone $horaAbertura;

        while ($horaAtual->lessThan($horaFechamento)) {
            $horaProxima = $horaAtual->copy()->addMinutes(30);

            $ocupado = $reservas->contains(function ($reserva) use ($horaAtual, $horaProxima) {
                $inicioReserva = Carbon::parse($reserva->hora_inicio);
                $fimReserva = Carbon::parse($reserva->hora_fim);

                return $inicioReserva->lessThan($horaProxima) && $fimReserva->greaterThan($horaAtual);
            });

            if (!$ocupado) {
                $horariosDisponiveis[] = $horaAtual->format('H:i');
            }

            $horaAtual = $horaProxima;
        }

        return response()->json($horariosDisponiveis);
    }
}
