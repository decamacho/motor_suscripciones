<?php

namespace App\Services;

use App\Models\ClienteSuscripcion;
use App\Models\CobroSuscripcion;

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

        $suscripciones = ClienteSuscripcion::query()
            ->with('suscripcion')
            ->where('estado_cliente_suscripcion', 'activa')
            ->where(function ($query) {
                $query->whereNull('fecha_proximo_cobro')
                    ->orWhere('fecha_proximo_cobro', '<=', now());
            })
            ->get();

        foreach ($suscripciones as $suscripcion) {
            if ($suscripcion->estado_cliente_suscripcion !== 'activa') {
                continue;
            }

            $pendiente = CobroSuscripcion::query()
                ->where('cliente_suscripcion_id', $suscripcion->cliente_suscripcion_id)
                ->where('cobro_estado', 'pendiente')
                ->first();

            if ($pendiente !== null) {
                if (!$this->estaPegado($pendiente)) {
                    continue;
                }

                $this->abandonarPendientePegado($suscripcion, $pendiente);
            }

            $respuesta = $this->crearYCobrar($suscripcion, $resultadoForzado);

            $resumen['procesadas']++;

            match ($respuesta['resultado']) {
                'aprobado' => $resumen['aprobadas']++,
                'rechazado' => $resumen['rechazadas']++,
                default => $resumen['tiempo_expirado']++,
            };
        }

        return $resumen;
    }

    public function cobrarSuscripcion(ClienteSuscripcion $suscripcion, ?string $resultadoForzado = null): array
    {
        $pendiente = CobroSuscripcion::query()
            ->where('cliente_suscripcion_id', $suscripcion->cliente_suscripcion_id)
            ->where('cobro_estado', 'pendiente')
            ->first();

        if ($pendiente !== null) {
            if (!$this->estaPegado($pendiente)) {
                return [
                    'cobrado' => false,
                    'motivo' => 'Ya existe un intento de cobro pendiente',
                    'resultado' => 'pendiente',
                ];
            }

            $this->abandonarPendientePegado($suscripcion, $pendiente);
        }

        $respuesta = $this->crearYCobrar($suscripcion, $resultadoForzado);

        return [
            'cobrado' => true,
            'resultado' => $respuesta['resultado'],
        ];
    }

    private function crearYCobrar(ClienteSuscripcion $suscripcion, ?string $resultadoForzado): array
    {
        $ultimo = CobroSuscripcion::query()
            ->where('cliente_suscripcion_id', $suscripcion->cliente_suscripcion_id)
            ->orderByDesc('cobro_intento_numero')
            ->first();

        $numero = $ultimo !== null && $ultimo->cobro_estado === 'fallido'
            ? (int) $ultimo->cobro_intento_numero + 1
            : 1;

        $cobro = CobroSuscripcion::query()->create([
            'cliente_suscripcion_id' => $suscripcion->cliente_suscripcion_id,
            'cobro_monto' => $suscripcion->suscripcion->suscripcion_precio,
            'cobro_estado' => 'pendiente',
            'cobro_intento_numero' => $numero,
            'cobro_fecha' => now(),
        ]);

        return $this->gateway->charge($cobro, $resultadoForzado);
    }

    private function estaPegado(CobroSuscripcion $pendiente): bool
    {
        $timeoutMin = (int) config('motor.cobro.timeout_min', 2);

        return $pendiente->cobro_resultado_pasarela === null
            && $pendiente->cobro_fecha !== null
            && $pendiente->cobro_fecha->lt(now()->subMinutes($timeoutMin));
    }

    private function abandonarPendientePegado(ClienteSuscripcion $suscripcion, CobroSuscripcion $pendiente): void
    {
        $superoIntentos = (int) $pendiente->cobro_intento_numero >= (int) config('motor.reintento.max_intentos', 3);

        $pendiente->update([
            'cobro_estado' => 'fallido',
            'cobro_resultado_pasarela' => 'timeout',
            'cobro_fecha' => now(),
        ]);

        $suscripcion->update([
            'estado_cliente_suscripcion' => $superoIntentos ? 'pausada' : 'activa',
            'fecha_proximo_cobro' => $superoIntentos
                ? null
                : now()->copy()->addMinutes((int) config('motor.reintento.intervalo_minutos', 2)),
        ]);
    }
}
