<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\User;
use Carbon\Carbon;

class ReservasController extends Controller
{
    public function index(Request $request)
    {
        $usuarioId = $request->query('usuario_id'); // Obtém o parâmetro 'usuario_id'

        if ($usuarioId) {
            $usuario = User::find($usuarioId);

            if (!$usuario) {
                return response()->json(['error' => 'Usuário não encontrado.'], 404);
            }

            if ($usuario->papel === 'admin') {
                // Admin pode ver todas as reservas ativas
                $reservas = Reserva::with('ambiente', 'usuario')
                    ->where('status', 'ativa')
                    ->get();
            } else {
                // Professores só veem suas próprias reservas ativas
                $reservas = Reserva::with('ambiente', 'usuario')
                    ->where('usuario_id', $usuario->id)
                    ->where('status', 'ativa')
                    ->get();
            }
        } else {
            // Se nenhum usuário for especificado, retorna todas as reservas ativas
            $reservas = Reserva::with('ambiente', 'usuario')
                ->where('status', 'ativa')
                ->get();
        }

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

        // Verifica se o usuário já possui uma reserva no mesmo dia
        $reservaMesmoDia = Reserva::where('usuario_id', $validated['usuario_id'])
            ->whereDate('hora_inicio', '=', date('Y-m-d', strtotime($validated['hora_inicio'])))
            ->exists();

        if ($reservaMesmoDia) {
            return response()->json(['error' => 'Você já possui uma reserva neste dia.'], 403);
        }

        // Verifica se o usuário já possui 2 reservas na mesma semana
        $dataInicio = Carbon::parse($validated['hora_inicio'])->startOfWeek(); // Início da semana
        $dataFim = Carbon::parse($validated['hora_inicio'])->endOfWeek(); // Fim da semana

        $reservasSemana = Reserva::where('usuario_id', $validated['usuario_id'])
            ->whereBetween('hora_inicio', [$dataInicio, $dataFim])
            ->count();

        if ($reservasSemana >= 2) { // Aqui garantimos que o limite é 2
            return response()->json(['error' => 'Você já atingiu o limite de 2 reservas nesta semana.'], 403);
        }

        // Criação da reserva
        $reserva = Reserva::create($validated);

        return response()->json($reserva, 201);
    }


    public function update(Request $request, $id)
    {
        $reserva = Reserva::findOrFail($id);

        $validated = $request->validate([
            'ambiente_id' => 'required|exists:ambientes,id',
            'hora_inicio' => 'required|date',
            'hora_fim' => 'required|date|after:hora_inicio',
        ]);

        // Verificar conflito de horário
        $conflito = Reserva::where('id', '!=', $id)
            ->where('ambiente_id', $validated['ambiente_id'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('hora_inicio', [$validated['hora_inicio'], $validated['hora_fim']])
                    ->orWhereBetween('hora_fim', [$validated['hora_inicio'], $validated['hora_fim']]);
            })
            ->exists();

        if ($conflito) {
            return response()->json(['error' => 'Esse horário já está reservado.'], 409);
        }

        // Verificar se o usuário já possui uma reserva no mesmo dia
        $reservaMesmoDia = Reserva::where('id', '!=', $id) // Ignorar a reserva atual
            ->where('usuario_id', $reserva->usuario_id)
            ->whereDate('hora_inicio', '=', date('Y-m-d', strtotime($validated['hora_inicio'])))
            ->exists();

        if ($reservaMesmoDia) {
            return response()->json(['error' => 'Você já possui uma reserva neste dia.'], 403);
        }

        // Verificar se o usuário já possui 2 reservas na semana do calendário
        $dataInicioSemana = Carbon::parse($validated['hora_inicio'])->startOfWeek(); // Segunda-feira
        $dataFimSemana = Carbon::parse($validated['hora_inicio'])->endOfWeek(); // Domingo

        $reservasSemana = Reserva::where('id', '!=', $id) // Ignorar a reserva atual
            ->where('usuario_id', $reserva->usuario_id)
            ->whereBetween('hora_inicio', [$dataInicioSemana, $dataFimSemana])
            ->count();

        if ($reservasSemana >= 2) {
            return response()->json(['error' => 'Você já atingiu o limite de 2 reservas nesta semana.'], 403);
        }

        // Atualizar a reserva
        $reserva->update($validated);

        return response()->json($reserva);
    }


    public function destroy(Request $request, $id)
    {
        $reserva = Reserva::findOrFail($id);
        $usuarioId = $request->input('usuario_id'); // Obtém o ID do usuário

        if (!$usuarioId) {
            return response()->json(['error' => 'ID do usuário não fornecido.'], 400);
        }

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

        $reserva->delete();
        return response()->json(['message' => 'Reserva excluída com sucesso.']);
    }

    // Calcular horários disponíveis para um ambiente
    public function horariosDisponiveis($ambienteId)
    {
        $reservas = Reserva::where('ambiente_id', $ambienteId)->get();

        $horaAbertura = Carbon::createFromTime(8, 0); // 08:00
        $horaFechamento = Carbon::createFromTime(18, 0); // 18:00
        $horariosDisponiveis = [];
        $horaAtual = clone $horaAbertura;

        while ($horaAtual->lessThan($horaFechamento)) {
            $horaProxima = $horaAtual->copy()->addMinutes(30);

            $ocupado = $reservas->some(function ($reserva) use ($horaAtual, $horaProxima) {
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
