<?php

namespace App\Http\Requests;

class StoreClienteRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_nombre' => ['required', 'string', 'max:100'],
            'cliente_correo' => ['required', 'string', 'email:rfc', 'max:150', 'unique:cliente,cliente_correo'],
            'cliente_documento' => ['required', 'string', 'digits:10', 'unique:cliente,cliente_documento'],
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
