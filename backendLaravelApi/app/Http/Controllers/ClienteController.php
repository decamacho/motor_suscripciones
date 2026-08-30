<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Support\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class ClienteController extends Controller
{
    public function index()
    {
        $listadoClientes = Cliente::withCount('suscripciones')->orderByDesc('created_at')->get();

        return ApiResponse::success($listadoClientes);
    }

    public function store(StoreClienteRequest $request)
    {
        $cliente = Cliente::create($request->validated());

        return ApiResponse::success(
            $cliente,
            'Cliente creado exitosamente',
            Response::HTTP_CREATED
        );
    }

    public function show(Cliente $cliente)
    {
        $cliente->load([
            'suscripciones' => fn ($query) => $query
                ->with('cobroSuscripciones')
                ->orderByDesc('created_at'),
        ]);

        return ApiResponse::success($cliente);
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        $cliente->update($request->validated());

        return ApiResponse::success(
            $cliente,
            'Cliente actualizado exitosamente'
        );
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return ApiResponse::success(
            null,
            'Cliente eliminado exitosamente'
        );
    }
}
