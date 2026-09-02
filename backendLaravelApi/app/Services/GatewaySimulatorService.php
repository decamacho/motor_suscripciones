<?php

namespace App\Services;

use App\Jobs\NotificarWebhookGateway;
use App\Models\CobroSuscripcion;

class GatewaySimulatorService
{
    public function charge(CobroSuscripcion $cobro, ?string $resultadoForzado = null): array
    {
        $resultado = strtolower($resultadoForzado ?? $this->resultadoAleatorio());

        NotificarWebhookGateway::dispatch($cobro->cobro_suscripcion_id, $resultado);

        return [
            'resultado' => $resultado,
            'cobro_suscripcion_id' => $cobro->cobro_suscripcion_id,
            'notificado' => true,
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
}
