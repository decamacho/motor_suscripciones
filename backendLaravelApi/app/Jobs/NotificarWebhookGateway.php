<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class NotificarWebhookGateway implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [5, 15];
    public $timeout = 30;

    public function __construct(
        public string $cobroSuscripcionId,
        public string $resultado
    ) {}

    public function handle(): void
    {
        Http::asJson()
            ->acceptJson()
            ->post(route('webhooks.gateway'), [
                'cobro_suscripcion_id' => $this->cobroSuscripcionId,
                'resultado' => $this->resultado,
            ])
            ->throw();
    }
}
