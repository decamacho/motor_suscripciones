<?php

namespace App\Services;

use App\Models\CobroSuscripcion;
use Illuminate\Support\Facades\Http;

class GatewaySimulatorService
{
    public function charge(CobroSuscripcion $cobro, ?string $resultadoForzado = null): array
    {
        $resultado = strtolower($resultadoForzado ?? $this->resultadoAleatorio());

        if ($resultado === 'timeout') {
            return [
                'resultado' => 'timeout',
                'cobro_suscripcion_id' => $cobro->cobro_suscripcion_id,
                'notificado' => false,
                'mensaje' => 'Hay problemas con la comunicacion de la pasarela',
            ];
        }

        $notificado = $this->notificarWebhook($cobro, $resultado);

        return [
            'resultado' => $resultado,
            'cobro_suscripcion_id' => $cobro->cobro_suscripcion_id,
            'notificado' => $notificado,
        ];
    }

    private function resultadoAleatorio(): string
    {
        $azar = random_int(1, 100);

        return match (true) {
            $azar <= 60 => 'aprobado',
            $azar <= 90 => 'rechazado',
            default => 'timeout',
        };
    }

    private function notificarWebhook(CobroSuscripcion $cobro, string $resultado): bool
    {
        try {
            Http::asJson()
                ->acceptJson()
                ->post(route('webhooks.gateway'), [
                    'cobro_suscripcion_id' => $cobro->cobro_suscripcion_id,
                    'resultado' => $resultado,
                ]);

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
