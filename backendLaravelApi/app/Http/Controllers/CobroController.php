<?php

namespace App\Http\Controllers;

use App\Http\Requests\EjecutarCobroRequest;
use App\Services\CobroMotorService;
use App\Support\ApiResponse;

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
}
