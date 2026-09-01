<?php

namespace App\Http\Controllers;

use App\Http\Requests\CobrarSuscripcionRequest;
use App\Http\Requests\EjecutarCobroRequest;
use App\Models\ClienteSuscripcion;
use App\Services\CobroMotorService;
use App\Support\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class CobroController extends Controller
{
    public function ejecutar(EjecutarCobroRequest $request, CobroMotorService $motor)
    {
        $resumen = $motor->ejecutar($request->validated()['resultado'] ?? null);

        return ApiResponse::success(
            $resumen,
            'Motor de cobro ejecutado exitosamente'
        );
    }

    public function cobrar(ClienteSuscripcion $clienteSuscripcion, CobrarSuscripcionRequest $request, CobroMotorService $motor)
    {
        if ($clienteSuscripcion->estado_cliente_suscripcion !== 'activa') {
            return ApiResponse::error(
                'La suscripción debe estar activa para poder cobrarla',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $resultado = $motor->cobrarSuscripcion(
            $clienteSuscripcion,
            $request->validated()['resultado'] ?? null
        );

        if (!$resultado['cobrado']) {
            return ApiResponse::error(
                $resultado['motivo'],
                Response::HTTP_CONFLICT
            );
        }

        return ApiResponse::success(
            $resultado,
            'Cobro procesado exitosamente'
        );
    }
}
