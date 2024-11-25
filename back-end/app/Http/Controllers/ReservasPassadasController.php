<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservasPassadasController extends Controller
{
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
                    ->where(function ($query) {
                        $query->whereNotNull('hora_fim')
                              ->where('hora_fim', '<', Carbon::now());
                    })
                    ->get();
            } else {
                $reservas = Reserva::with('ambiente', 'usuario')
                    ->where('usuario_id', $usuario->id)
                    ->where(function ($query) {
                        $query->whereNotNull('hora_fim')
                              ->where('hora_fim', '<', Carbon::now());
                    })
                    ->get();
            }
        } else {
            $reservas = Reserva::with('ambiente', 'usuario')
                ->where(function ($query) {
                    $query->whereNotNull('hora_fim')
                          ->where('hora_fim', '<', Carbon::now());
                })
                ->get();
        }

        return response()->json($reservas);
    }
}
