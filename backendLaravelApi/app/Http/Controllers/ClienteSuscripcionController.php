<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteSuscripcionRequest;
use App\Http\Requests\UpdateClienteSuscripcionRequest;
use App\Models\ClienteSuscripcion;
use App\Support\ApiResponse;
use App\Support\ClienteSuscripcionFormatter;
use Symfony\Component\HttpFoundation\Response;

class ClienteSuscripcionController extends Controller
{
    public function index()
    {
        $suscripciones = ClienteSuscripcion::query()
            ->with(['cliente', 'suscripcion'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ClienteSuscripcion $s) => ClienteSuscripcionFormatter::relacion($s));

        return ApiResponse::success($suscripciones);
    }

    public function store(StoreClienteSuscripcionRequest $request)
    {
        $suscripcion = ClienteSuscripcion::create($request->validated());

        return ApiResponse::success(
            $suscripcion->load(['cliente', 'suscripcion']),
            'Suscripción asignada al cliente exitosamente',
            Response::HTTP_CREATED
        );
    }

    public function show(ClienteSuscripcion $clienteSuscripcion)
    {
        $clienteSuscripcion->load([
            'cliente',
            'suscripcion',
            'cobroSuscripciones.clienteSuscripcion.cliente',
            'cobroSuscripciones.clienteSuscripcion.suscripcion',
        ]);

        return ApiResponse::success(
            ClienteSuscripcionFormatter::relacion($clienteSuscripcion, conCobros: true)
        );
    }

    public function update(UpdateClienteSuscripcionRequest $request, ClienteSuscripcion $clienteSuscripcion)
    {
        $clienteSuscripcion->update($request->validated());

        return ApiResponse::success(
            $clienteSuscripcion->load(['cliente', 'suscripcion']),
            'Suscripción del cliente actualizada exitosamente'
        );
    }

    public function cobros(ClienteSuscripcion $clienteSuscripcion)
    {
        $cobros = $clienteSuscripcion->cobroSuscripciones()
            ->with(['clienteSuscripcion.cliente', 'clienteSuscripcion.suscripcion'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($cobro) => ClienteSuscripcionFormatter::cobro($cobro));

        return ApiResponse::success($cobros);
    }

    public function destroy(ClienteSuscripcion $clienteSuscripcion)
    {
        $clienteSuscripcion->delete();

        return ApiResponse::success(
            null,
            'Suscripción del cliente eliminada exitosamente'
        );
    }
}
