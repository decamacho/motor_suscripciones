<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\SuscripcionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/clientes', [ClienteController::class, 'index']);
Route::post('/cliente', [ClienteController::class, 'store']);
Route::post('/clientes', [ClienteController::class, 'store']);
Route::get('/clientes/{cliente}', [ClienteController::class, 'show']);
Route::put('/clientes/{cliente}', [ClienteController::class, 'update']);
Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy']);

Route::get('/suscripciones', [SuscripcionController::class, 'index']);
Route::post('/suscripciones', [SuscripcionController::class, 'store']);
Route::get('/suscripciones/{suscripcion}', [SuscripcionController::class, 'show']);
Route::put('/suscripciones/{suscripcion}', [SuscripcionController::class, 'update']);
Route::patch('/suscripciones/{suscripcion}/estado', [SuscripcionController::class, 'cambiarEstado']);
Route::get('/suscripciones/{suscripcion}/cobros', [SuscripcionController::class, 'cobros']);
Route::delete('/suscripciones/{suscripcion}', [SuscripcionController::class, 'destroy']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
