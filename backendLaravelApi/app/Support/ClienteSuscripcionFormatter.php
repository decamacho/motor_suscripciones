<?php

namespace App\Support;

use App\Models\ClienteSuscripcion;
use App\Models\CobroSuscripcion;

class ClienteSuscripcionFormatter
{
    public static function relacion(ClienteSuscripcion $relacion, bool $conCobros = false): array
    {
        $plan = $relacion->suscripcion;
        $cliente = $relacion->cliente;

        $datos = [
            'cliente_suscripcion_id' => $relacion->cliente_suscripcion_id,
            'cliente_id' => $relacion->cliente_id,
            'cliente_nombre' => $cliente?->cliente_nombre,
            'suscripcion_id' => $relacion->suscripcion_id,
            'suscripcion_nombre' => $plan?->suscripcion_nombre,
            'suscripcion_descripcion' => $plan?->suscripcion_descripcion,
            'suscripcion_precio' => $plan?->suscripcion_precio,
            'suscripcion_periodo' => $plan?->suscripcion_periodo,
            'estado_cliente_suscripcion' => $relacion->estado_cliente_suscripcion,
            'fecha_ultimo_cobro' => $relacion->fecha_ultimo_cobro,
            'fecha_proximo_cobro' => $relacion->fecha_proximo_cobro,
            'created_at' => $relacion->created_at,
            'updated_at' => $relacion->updated_at,
        ];

        if ($conCobros) {
            $datos['cobros'] = $relacion->cobroSuscripciones
                ->sortByDesc('created_at')
                ->values()
                ->map(fn (CobroSuscripcion $cobro) => self::cobro($cobro))
                ->all();
        }

        return $datos;
    }

    public static function cobro(CobroSuscripcion $cobro): array
    {
        $relacion = $cobro->clienteSuscripcion;
        $plan = $relacion?->suscripcion;

        return [
            'cobro_suscripcion_id' => $cobro->cobro_suscripcion_id,
            'cobro_monto' => $cobro->cobro_monto,
            'cobro_estado' => $cobro->cobro_estado,
            'cobro_intento_numero' => $cobro->cobro_intento_numero,
            'cobro_resultado_pasarela' => $cobro->cobro_resultado_pasarela,
            'cobro_fecha' => $cobro->cobro_fecha,
            'cliente_nombre' => $relacion?->cliente?->cliente_nombre,
            'suscripcion_id' => $relacion?->suscripcion_id,
            'suscripcion_nombre' => $plan?->suscripcion_nombre,
            'suscripcion_descripcion' => $plan?->suscripcion_descripcion,
            'suscripcion_precio' => $plan?->suscripcion_precio,
            'suscripcion_periodo' => $plan?->suscripcion_periodo,
            'estado_cliente_suscripcion' => $relacion?->estado_cliente_suscripcion,
            'fecha_ultimo_cobro' => $relacion?->fecha_ultimo_cobro,
            'fecha_proximo_cobro' => $relacion?->fecha_proximo_cobro,
        ];
    }
}
