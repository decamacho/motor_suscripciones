<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebhookGatewayRequest;
use App\Services\GatewayWebhookService;
use App\Support\ApiResponse;

class WebhookController extends Controller
{
    public function gateway(WebhookGatewayRequest $request, GatewayWebhookService $webhook)
    {
        $proceso = $webhook->procesar(
            $request->validated()['cobro_suscripcion_id'],
            $request->validated()['resultado']
        );

        return ApiResponse::success(
            $proceso,
            'Webhook de la pasarela procesado'
        );
    }
}
