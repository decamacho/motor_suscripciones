<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Suscripcion extends Model
{
    use HasUuids;

    protected $table = 'suscripcion';

    protected $primaryKey = 'suscripcion_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'suscripcion_id',
        'cliente_id',
        'suscripcion_nombre',
        'suscripcion_descripcion',
        'suscripcion_precio',
        'suscripcion_periodo',
        'suscripcion_estado',
        'ultimo_cobro_at',
        'proximo_cobro_at',
    ];

    protected function casts(): array
    {
        return [
            'suscripcion_precio' => 'integer',
            'ultimo_cobro_at' => 'datetime',
            'proximo_cobro_at' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'cliente_id');
    }

    public function cobroSuscripciones(): HasMany
    {
        return $this->hasMany(CobroSuscripcion::class, 'suscripcion_id', 'suscripcion_id');
    }
}
