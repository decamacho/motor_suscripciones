<?php

namespace App\Console\Commands;

use App\Services\CobroMotorService;
use Illuminate\Console\Command;

class EjecutarCobroCommand extends Command
{
    protected $signature = 'cobro:ejecutar
                            {--resultado= : Forzar el resultado (aprobado|rechazado|timeout)}';

    protected $description = 'Ejecuta el motor de cobro para todas las suscripciones activas';

    public function handle(CobroMotorService $motor): int
    {
        $resumen = $motor->ejecutar($this->option('resultado'));

        foreach ($resumen as $clave => $valor) {
            $this->line(sprintf('%s: %d', $clave, $valor));
        }

        return self::SUCCESS;
    }
}
