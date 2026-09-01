<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ClienteSuscripcionController;
use App\Http\Controllers\CobroController;
use App\Http\Controllers\PasarelaController;
use App\Http\Controllers\SuscripcionController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// metodos clientes
Route::get('/clientes', [ClienteController::class, 'index']);
Route::post('/clientes', [ClienteController::class, 'store']);
Route::get('/clientes/{cliente}', [ClienteController::class, 'show']);
Route::put('/clientes/{cliente}', [ClienteController::class, 'update']);
Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy']);

// metodos suscripciones
Route::get('/suscripciones', [SuscripcionController::class, 'index']);
Route::post('/suscripciones', [SuscripcionController::class, 'store']);
Route::get('/suscripciones/{suscripcion}', [SuscripcionController::class, 'show']);
Route::put('/suscripciones/{suscripcion}', [SuscripcionController::class, 'update']);
Route::delete('/suscripciones/{suscripcion}', [SuscripcionController::class, 'destroy']);

// metodos suscripciones/clientes
Route::get('/cliente-suscripciones', [ClienteSuscripcionController::class, 'index']);
Route::post('/cliente-suscripciones', [ClienteSuscripcionController::class, 'store']);
Route::get('/cliente-suscripciones/{clienteSuscripcion}', [ClienteSuscripcionController::class, 'show']);
Route::put('/cliente-suscripciones/{clienteSuscripcion}', [ClienteSuscripcionController::class, 'update']);
Route::get('/cliente-suscripciones/{clienteSuscripcion}/cobros', [ClienteSuscripcionController::class, 'cobros']);
Route::delete('/cliente-suscripciones/{clienteSuscripcion}', [ClienteSuscripcionController::class, 'destroy']);

// cobro puntual de una suscripcion
Route::post('/cliente-suscripciones/{clienteSuscripcion}/cobrar', [CobroController::class, 'cobrar']);

// proceso de cobros y pasarela simulada
Route::post('/cobro/ejecutar', [CobroController::class, 'ejecutar']);
Route::post('/pasarela/cobrar', [PasarelaController::class, 'cobrar'])->name('pasarela.cobrar');
Route::post('/webhooks/gateway', [WebhookController::class, 'gateway'])->name('webhooks.gateway');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
