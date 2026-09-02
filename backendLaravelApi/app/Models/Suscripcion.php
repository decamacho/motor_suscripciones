<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
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
        'suscripcion_nombre',
        'suscripcion_descripcion',
        'suscripcion_precio',
        'suscripcion_periodo',
    ];

    protected function casts(): array
    {
        return [
            'suscripcion_precio' => 'integer',
        ];
    }

    public function clienteSuscripciones(): HasMany
    {
        return $this->hasMany(ClienteSuscripcion::class, 'suscripcion_id', 'suscripcion_id');
    }
}
