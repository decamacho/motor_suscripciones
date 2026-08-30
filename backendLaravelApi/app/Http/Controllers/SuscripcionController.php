<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSuscripcionRequest;
use App\Http\Requests\UpdateEstadoSuscripcionRequest;
use App\Http\Requests\UpdateSuscripcionRequest;
use App\Models\Suscripcion;
use App\Support\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class SuscripcionController extends Controller
{
    public function index()
    {
        $suscripciones = Suscripcion::with('cliente')->orderByDesc('created_at')->get();

        return ApiResponse::success($suscripciones);
    }

    public function store(StoreSuscripcionRequest $request)
    {
        $suscripcion = Suscripcion::create($request->validated());

        return ApiResponse::success(
            $suscripcion->load('cliente'),
            'Suscripción creada exitosamente',
            Response::HTTP_CREATED
        );
    }

    public function show(Suscripcion $suscripcion)
    {
        $suscripcion->load(['cliente', 'cobroSuscripciones' => fn ($query) => $query->orderByDesc('created_at')]);

        return ApiResponse::success($suscripcion);
    }

    public function update(UpdateSuscripcionRequest $request, Suscripcion $suscripcion)
    {
        $suscripcion->update($request->validated());

        return ApiResponse::success(
            $suscripcion->load('cliente'),
            'Suscripción actualizada exitosamente'
        );
    }

    public function cambiarEstado(UpdateEstadoSuscripcionRequest $request, Suscripcion $suscripcion)
    {
        $suscripcion->update([
            'suscripcion_estado' => $request->validated()['suscripcion_estado'],
        ]);

        return ApiResponse::success(
            $suscripcion->load('cliente'),
            'Estado de la suscripción actualizado'
        );
    }

    public function cobros(Suscripcion $suscripcion)
    {
        $cobros = $suscripcion->cobroSuscripciones()->orderByDesc('created_at')->get();

        return ApiResponse::success($cobros);
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
