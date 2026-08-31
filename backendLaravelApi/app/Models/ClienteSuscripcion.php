<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClienteSuscripcion extends Model
{
    use HasUuids;

    protected $table = 'cliente_suscripcion';

    protected $primaryKey = 'cliente_suscripcion_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cliente_suscripcion_id',
        'cliente_id',
        'suscripcion_id',
        'estado_cliente_suscripcion',
        'fecha_ultimo_cobro',
        'fecha_proximo_cobro',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ultimo_cobro' => 'datetime',
            'fecha_proximo_cobro' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'cliente_id');
    }

    public function suscripcion(): BelongsTo
    {
        return $this->belongsTo(Suscripcion::class, 'suscripcion_id', 'suscripcion_id');
    }

    public function cobroSuscripciones(): HasMany
    {
        return $this->hasMany(CobroSuscripcion::class, 'cliente_suscripcion_id', 'cliente_suscripcion_id');
    }
}
