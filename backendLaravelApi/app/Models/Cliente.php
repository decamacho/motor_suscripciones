<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Cliente extends Model
{
    use HasUuids;

    protected $table = 'cliente';

    protected $primaryKey = 'cliente_id';

    // indica que no es un entero autoincrementable
    public $incrementing = false;

    // indica que el tipo de llave es un string
    protected $keyType = 'string';

    protected $fillable = [
        'cliente_id',
        'cliente_nombre',
        'cliente_correo',
        'cliente_documento',
        'cliente_telefono',
    ];
}
