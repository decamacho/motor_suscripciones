<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSuscripcionRequest;
use App\Http\Requests\UpdateSuscripcionRequest;
use App\Models\Suscripcion;
use App\Support\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class SuscripcionController extends Controller
{
    public function index()
    {
        $suscripciones = Suscripcion::query()->orderByDesc('created_at')->get();

        return ApiResponse::success($suscripciones);
    }

    public function store(StoreSuscripcionRequest $request)
    {
        $suscripcion = Suscripcion::create($request->validated());

        return ApiResponse::success(
            $suscripcion,
            'Suscripción creada exitosamente',
            Response::HTTP_CREATED
        );
    }

    public function show(Suscripcion $suscripcion)
    {
        return ApiResponse::success($suscripcion);
    }

    public function update(UpdateSuscripcionRequest $request, Suscripcion $suscripcion)
    {
        $suscripcion->update($request->validated());

        return ApiResponse::success(
            $suscripcion->refresh(),
            'Suscripción actualizada exitosamente'
        );
    }

    public function destroy(Suscripcion $suscripcion)
    {
        $suscripcion->delete();

        return ApiResponse::success(
            null,
            'Suscripción eliminada exitosamente'
        );
    }
}
