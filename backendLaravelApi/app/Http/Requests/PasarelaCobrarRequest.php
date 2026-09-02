<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class PasarelaCobrarRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'cobro_suscripcion_id' => ['required', 'uuid', Rule::exists('cobro_suscripcion', 'cobro_suscripcion_id')],
            'resultado' => ['nullable', Rule::in(['aprobado', 'rechazado', 'timeout'])],
        ];
    }

    public function messages(): array
    {
        return [
            'cobro_suscripcion_id.required' => 'Debe indicar el intento de cobro a procesar',
            'cobro_suscripcion_id.uuid' => 'El identificador del intento de cobro no es válido',
            'cobro_suscripcion_id.exists' => 'El intento de cobro indicado no existe',
            'resultado.in' => 'El resultado solo puede ser aprobado, rechazado o timeout',
        ];
    }
}
