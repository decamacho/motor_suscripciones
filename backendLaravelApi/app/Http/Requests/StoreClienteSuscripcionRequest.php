<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreClienteSuscripcionRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'uuid', Rule::exists('cliente', 'cliente_id')],
            'suscripcion_id' => ['required', 'uuid', Rule::exists('suscripcion', 'suscripcion_id')],
            'estado_cliente_suscripcion' => ['sometimes', Rule::in(['activa', 'pausada', 'cancelada'])],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Debe indicar el cliente',
            'cliente_id.exists' => 'El cliente seleccionado no es válido',
            'suscripcion_id.required' => 'Debe indicar la suscripción',
            'suscripcion_id.exists' => 'La suscripción seleccionada no es válida',
            'estado_cliente_suscripcion.in' => 'El estado debe ser activa, pausada o cancelada',
        ];
    }
}
