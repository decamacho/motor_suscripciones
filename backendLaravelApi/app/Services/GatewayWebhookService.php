<?php

namespace App\Services;

use App\Models\ClienteSuscripcion;
use App\Models\CobroSuscripcion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GatewayWebhookService
{
    public function procesar(string $cobroSuscripcionId, string $resultado): array
    {
        return DB::transaction(function () use ($cobroSuscripcionId, $resultado) {
            $cobro = CobroSuscripcion::query()->lockForUpdate()->findOrFail($cobroSuscripcionId);

            $clienteSuscripcion = ClienteSuscripcion::query()
                ->lockForUpdate()
                ->with('suscripcion')
                ->findOrFail($cobro->cliente_suscripcion_id);

            if ($cobro->cobro_estado !== 'pendiente') {
                return [
                    'procesado' => false,
                    'motivo' => 'El intento ya cuenta con un estado final',
                    'cobro_suscripcion_id' => $cobro->cobro_suscripcion_id,
                    'estado' => $cobro->cobro_estado,
                ];
            }

            $ahora = now();

            if ($resultado === 'aprobado') {
                $cobro->update([
                    'cobro_estado' => 'exitoso',
                    'cobro_resultado_pasarela' => 'aprobado',
                    'cobro_fecha' => $ahora,
                ]);

                $clienteSuscripcion->update([
                    'estado_cliente_suscripcion' => 'activa',
                    'fecha_ultimo_cobro' => $ahora,
                    'fecha_proximo_cobro' => $this->proximaFechaDeCobro($clienteSuscripcion->suscripcion->suscripcion_periodo, $ahora),
                ]);

                return [
                    'procesado' => true,
                    'cobro_suscripcion_id' => $cobro->cobro_suscripcion_id,
                    'estado' => 'exitoso',
                ];
            }

            $superoIntentos = (int) $cobro->cobro_intento_numero >= (int) config('motor.reintento.max_intentos', 3);

            $cobro->update([
                'cobro_estado' => 'fallido',
                'cobro_resultado_pasarela' => $resultado,
                'cobro_fecha' => $ahora,
            ]);

            $clienteSuscripcion->update([
                'estado_cliente_suscripcion' => $superoIntentos ? 'pausada' : 'activa',
                'fecha_proximo_cobro' => $superoIntentos
                    ? null
                    : $ahora->copy()->addMinutes((int) config('motor.reintento.intervalo_minutos', 2)),
            ]);

            return [
                'procesado' => true,
                'cobro_suscripcion_id' => $cobro->cobro_suscripcion_id,
                'estado' => 'fallido',
                'cliente_suscripcion_pausada' => $superoIntentos,
            ];
        });
    }

    private function proximaFechaDeCobro(string $periodo, Carbon $desde): Carbon
    {
        $minutos = (int) config("motor.periodos.{$periodo}", 30);

        return $desde->copy()->addMinutes($minutos);
    }
}
