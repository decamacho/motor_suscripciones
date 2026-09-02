<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateClienteSuscripcionRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'estado_cliente_suscripcion' => ['sometimes', Rule::in(['activa', 'pausada', 'cancelada'])],
            'fecha_ultimo_cobro' => ['nullable', 'date'],
            'fecha_proximo_cobro' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'estado_cliente_suscripcion.in' => 'El estado debe ser activa, pausada o cancelada',
            'fecha_ultimo_cobro.date' => 'La fecha de último cobro no es válida',
            'fecha_proximo_cobro.date' => 'La fecha de próximo cobro no es válida',
        ];
    }
}
