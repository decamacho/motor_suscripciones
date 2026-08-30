<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CobroSuscripcion extends Model
{
    use HasUuids;

    protected $table = 'cobro_suscripcion';

    protected $primaryKey = 'cobro_suscripcion_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cobro_suscripcion_id',
        'suscripcion_id',
        'cobro_monto',
        'cobro_estado',
        'cobro_intento_numero',
        'cobro_resultado_pasarela',
        'cobro_fecha',
    ];

    protected function casts(): array
    {
        return [
            'cobro_monto' => 'integer',
            'cobro_fecha' => 'datetime',
        ];
    }

    public function suscripcion(): BelongsTo
    {
        return $this->belongsTo(Suscripcion::class, 'suscripcion_id', 'suscripcion_id');
    }
}
