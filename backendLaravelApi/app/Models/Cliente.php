<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasUuids;

    protected $table = 'cliente';

    protected $primaryKey = 'cliente_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cliente_id',
        'cliente_nombre',
        'cliente_correo',
        'cliente_documento',
        'cliente_telefono',
    ];

    public function clienteSuscripciones(): HasMany
    {
        return $this->hasMany(ClienteSuscripcion::class, 'cliente_id', 'cliente_id');
    }

    public function suscripciones(): BelongsToMany
    {
        return $this->belongsToMany(Suscripcion::class, 'cliente_suscripcion', 'cliente_id', 'suscripcion_id', 'cliente_id', 'suscripcion_id')
            ->withPivot(['estado_cliente_suscripcion', 'fecha_ultimo_cobro', 'fecha_proximo_cobro'])
            ->withTimestamps();
    }
}
