<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreSuscripcionRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'suscripcion_nombre' => ['required', 'string', 'max:100'],
            'suscripcion_descripcion' => ['nullable', 'string', 'max:255'],
            'suscripcion_precio' => ['required', 'integer', 'min:1'],
            'suscripcion_periodo' => ['required', Rule::in(['mensual', 'anual'])],
        ];
    }

    public function messages(): array
    {
        return [
            'suscripcion_nombre.required' => 'El nombre de la suscripción es obligatorio',
            'suscripcion_nombre.max' => 'El nombre no debe superar los 100 caracteres',
            'suscripcion_descripcion.max' => 'La descripción no debe superar los 255 caracteres',
            'suscripcion_precio.required' => 'El precio es obligatorio',
            'suscripcion_precio.integer' => 'El precio debe ser un valor entero',
            'suscripcion_precio.min' => 'El precio debe ser mayor a cero',
            'suscripcion_periodo.required' => 'Debe indicar la periodicidad de cobro',
            'suscripcion_periodo.in' => 'La periodicidad debe ser mensual o anual',
        ];
    }
}
