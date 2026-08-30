<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Models\Cliente;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClienteController extends Controller
{

    public function index()
    {

        $listadoClientes = Cliente::all();

        return ApiResponse::success($listadoClientes);
    }

    public function create()
    {
        //
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
        //
    }


    public function edit(Cliente $cliente)
    {
        //
    }


    public function update(Request $request, Cliente $cliente)
    {
        //
    }


    public function destroy(Cliente $cliente)
    {
        //
    }
}
