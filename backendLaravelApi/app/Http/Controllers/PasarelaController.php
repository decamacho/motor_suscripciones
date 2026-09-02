<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasarelaCobrarRequest;
use App\Models\CobroSuscripcion;
use App\Services\GatewaySimulatorService;
use App\Support\ApiResponse;

class PasarelaController extends Controller
{
    public function cobrar(PasarelaCobrarRequest $request, GatewaySimulatorService $gateway)
    {
        $cobro = CobroSuscripcion::query()->findOrFail($request->validated()['cobro_suscripcion_id']);

        $respuesta = $gateway->charge($cobro, $request->validated()['resultado'] ?? null);

        return ApiResponse::success(
            $respuesta,
            'Solicitud de cargo procesada por la pasarela'
        );
    }
}
