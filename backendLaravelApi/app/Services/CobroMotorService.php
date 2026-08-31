<?php

namespace App\Services;

use App\Models\CobroSuscripcion;
use App\Models\Suscripcion;

class CobroMotorService
{
    public function __construct(
        private readonly GatewaySimulatorService $gateway,
    ) {}

    public function ejecutar(?string $resultadoForzado = null): array
    {
        $resumen = [
            'procesadas' => 0,
            'aprobadas' => 0,
            'rechazadas' => 0,
            'tiempo_expirado' => 0,
        ];

        $suscripciones = Suscripcion::query()
            ->where('suscripcion_estado', 'activa')
            ->where(function ($query) {
                $query->whereNull('proximo_cobro_at')
                    ->orWhere('proximo_cobro_at', '<=', now());
            })
            ->get();

        foreach ($suscripciones as $suscripcion) {
            $hayPendiente = CobroSuscripcion::query()
                ->where('suscripcion_id', $suscripcion->suscripcion_id)
                ->where('cobro_estado', 'pendiente')
                ->exists();

            if ($hayPendiente) {
                continue;
            }

            $ultimo = CobroSuscripcion::query()
                ->where('suscripcion_id', $suscripcion->suscripcion_id)
                ->orderByDesc('cobro_intento_numero')
                ->first();

            $numero = $ultimo !== null && $ultimo->cobro_estado === 'fallido'
                ? (int) $ultimo->cobro_intento_numero + 1
                : 1;

            $cobro = CobroSuscripcion::query()->create([
                'suscripcion_id' => $suscripcion->suscripcion_id,
                'cobro_monto' => $suscripcion->suscripcion_precio,
                'cobro_estado' => 'pendiente',
                'cobro_intento_numero' => $numero,
                'cobro_fecha' => now(),
            ]);

            $resumen['procesadas']++;

            $respuesta = $this->gateway->charge($cobro, $resultadoForzado);

            match ($respuesta['resultado']) {
                'aprobado' => $resumen['aprobadas']++,
                'rechazado' => $resumen['rechazadas']++,
                default => $resumen['tiempo_expirado']++,
            };
        }

        return $resumen;
    }
}
