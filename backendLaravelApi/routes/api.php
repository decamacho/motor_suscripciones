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
Route::post('/cliente', [ClienteController::class, 'store']);
Route::get('/cliente/{cliente}', [ClienteController::class, 'show']);
Route::put('/cliente/{cliente}', [ClienteController::class, 'update']);
Route::delete('/cliente/{cliente}', [ClienteController::class, 'destroy']);

// metodos suscripciones
Route::get('/suscripciones', [SuscripcionController::class, 'index']);
Route::post('/suscripcion', [SuscripcionController::class, 'store']);
Route::get('/suscripcion/{suscripcion}', [SuscripcionController::class, 'show']);
Route::put('/suscripcion/{suscripcion}', [SuscripcionController::class, 'update']);
Route::delete('/suscripcion/{suscripcion}', [SuscripcionController::class, 'destroy']);

// metodos suscripciones/clientes
Route::get('/cliente-suscripciones', [ClienteSuscripcionController::class, 'index']);
Route::post('/cliente-suscripcion', [ClienteSuscripcionController::class, 'store']);
Route::get('/cliente-suscripcion/{clienteSuscripcion}', [ClienteSuscripcionController::class, 'show']);
Route::put('/cliente-suscripcion/{clienteSuscripcion}', [ClienteSuscripcionController::class, 'update']);
Route::get('/cliente-suscripcion/{clienteSuscripcion}/cobro', [ClienteSuscripcionController::class, 'cobros']);
Route::delete('/cliente-suscripcion/{clienteSuscripcion}', [ClienteSuscripcionController::class, 'destroy']);

// proceso de cobros y pasarela simulada
Route::post('/cobro/ejecutar', [CobroController::class, 'ejecutar']);
Route::post('/pasarela/cobrar', [PasarelaController::class, 'cobrar'])->name('pasarela.cobrar');
Route::post('/webhooks/gateway', [WebhookController::class, 'gateway'])->name('webhooks.gateway');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
