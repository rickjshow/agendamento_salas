<?php

use App\Http\Controllers\AmbienteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GerenciamentoUsuariosController;
<<<<<<< HEAD
use App\Http\Controllers\NotificacaoController;
=======
use App\Http\Controllers\ReservasController;
>>>>>>> b0853e8da32357be87a8a07b77cf5be2d6104ae0
use Illuminate\Support\Facades\Route;

Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

Route::post('/login', [AuthController::class, 'login']);

Route::post('/alterar-senha/{id}', [AuthController::class, 'alterarSenha']);
Route::post('/usuarios/reset-password/{id}', [GerenciamentoUsuariosController::class, 'resetPassword']);
Route::get('/usuarios', [GerenciamentoUsuariosController::class, 'index']);
Route::post('/usuarios/store', [GerenciamentoUsuariosController::class, 'store']);
Route::put('/usuarios/{id}/edit', [GerenciamentoUsuariosController::class, 'update']);
Route::delete('/usuarios/{id}', [GerenciamentoUsuariosController::class, 'destroy']);
/*API DE AMBIENTES*/
Route::get('/ambientes', [AmbienteController::class, 'index']);
Route::post('/ambientes/store', [AmbienteController::class, 'store']);
Route::get('/ambientes/{id}', [AmbienteController::class, 'show']);
Route::put('/ambientes/{id}/edit', [AmbienteController::class, 'update']);
Route::delete('/ambientes/{id}', [AmbienteController::class, 'destroy']);

<<<<<<< HEAD
/*API DE NOTIFICAÇÕES*/
Route::get('/notificacoes/{id}', [NotificacaoController::class, 'index']);


=======
Route::get('/reservas', [ReservasController::class, 'index']);
Route::post('/reservas/store', [ReservasController::class, 'store']);
Route::put('/reservas/{id}/edit', [ReservasController::class, 'update']);
Route::delete('/reservas/{id}', [ReservasController::class, 'destroy']);
Route::get('/reservas/disponiveis/{ambienteId}', [ReservasController::class, 'horariosDisponiveis']);
>>>>>>> b0853e8da32357be87a8a07b77cf5be2d6104ae0
