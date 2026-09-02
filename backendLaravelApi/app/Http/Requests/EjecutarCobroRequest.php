<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class EjecutarCobroRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'resultado' => ['nullable', Rule::in(['aprobado', 'rechazado', 'timeout'])],
        ];
    }

    public function messages(): array
    {
        return [
            'resultado.in' => 'El resultado solo puede ser aprobado, rechazado o timeout',
        ];
    }
}
