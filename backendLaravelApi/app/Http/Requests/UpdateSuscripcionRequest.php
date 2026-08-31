<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateSuscripcionRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'suscripcion_nombre' => ['sometimes', 'string', 'max:100'],
            'suscripcion_descripcion' => ['nullable', 'string', 'max:255'],
            'suscripcion_precio' => ['sometimes', 'integer', 'min:1'],
            'suscripcion_periodo' => ['sometimes', Rule::in(['mensual', 'anual'])],
        ];
    }

    public function messages(): array
    {
        return [
            'suscripcion_nombre.max' => 'El nombre no debe superar los 100 caracteres',
            'suscripcion_descripcion.max' => 'La descripción no debe superar los 255 caracteres',
            'suscripcion_precio.integer' => 'El precio debe ser un valor entero',
            'suscripcion_precio.min' => 'El precio debe ser mayor a cero',
            'suscripcion_periodo.in' => 'La periodicidad debe ser mensual o anual',
        ];
    }
}
