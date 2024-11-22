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

        if (!$usuarioId) {
            return response()->json(['error' => 'Usuário não fornecido.'], 400);
        }

        $usuario = User::find($usuarioId);

        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado.'], 404);
        }

        if ($usuario->papel === 'admin') {
            // Admin pode ver todas as reservas
            $reservas = Reserva::with('ambiente', 'usuario')->get();
        } else {
            // Professores só veem suas próprias reservas
            $reservas = Reserva::with('ambiente', 'usuario')
                ->where('usuario_id', $usuario->id)
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
                $query->whereBetween('hora_inicio', [$validated['hora_inicio'], $validated['hora_fim']])
                      ->orWhereBetween('hora_fim', [$validated['hora_inicio'], $validated['hora_fim']])
                      ->orWhere(function ($q) use ($validated) {
                          $q->where('hora_inicio', '<', $validated['hora_inicio'])
                            ->where('hora_fim', '>', $validated['hora_fim']);
                      });
            })
            ->exists();

        if ($conflito) {
            return response()->json(['error' => 'Esse horário ja está reservado.'], 409);
        }

        // Criação da reserva
        $reserva = Reserva::create($validated);

        return response()->json($reserva, 201);
    }


    // Atualizar uma reserva
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
            return response()->json(['error' => 'Esse horario está reservado.'], 409);
        }

        $reserva->update($validated);
        return response()->json($reserva);
    }

    // Excluir uma reserva
    public function destroy($id)
    {
        $reserva = Reserva::findOrFail($id);
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
