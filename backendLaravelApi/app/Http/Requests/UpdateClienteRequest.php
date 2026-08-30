<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateClienteRequest extends BaseRequest
{
    public function rules(): array
    {
        $clienteId = $this->route('cliente')?->getKey();

        return [
            'cliente_nombre' => ['required', 'string', 'max:100'],
            'cliente_correo' => ['required', 'string', 'email:rfc', 'max:150', Rule::unique('cliente', 'cliente_correo')->ignore($clienteId, 'cliente_id')],
            'cliente_documento' => ['required', 'string', 'digits:10', Rule::unique('cliente', 'cliente_documento')->ignore($clienteId, 'cliente_id')],
            'cliente_telefono' => ['required', 'string', 'digits:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_documento.digits' => 'El documento debe contener exactamente 10 digitos',
            'cliente_telefono.digits' => 'El telefono debe contener exactamente 10 digitos',
            'cliente_correo.unique' => 'El correo ya se encuentra registrado',
            'cliente_documento.unique' => 'El documento ya se encuentra registrado',
            'cliente_telefono.unique' => 'El telefono ya se encuentra registrado',
        ];
    }
}
