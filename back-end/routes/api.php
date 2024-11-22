<?php

use App\Http\Controllers\AmbienteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GerenciamentoUsuariosController;
use App\Http\Controllers\NotificacaoController;
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

/*API DE NOTIFICAÇÕES*/
Route::get('/notificacoes/{id}', [NotificacaoController::class, 'index']);


