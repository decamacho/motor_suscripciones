<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateEstadoSuscripcionRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'suscripcion_estado' => ['required', Rule::in(['activa', 'pausada', 'cancelada'])],
        ];
    }

    public function messages(): array
    {
        return [
            'suscripcion_estado.required' => 'Debe indicar el estado de la suscripción',
            'suscripcion_estado.in' => 'El estado debe ser activa, pausada o cancelada',
        ];
    }
}
