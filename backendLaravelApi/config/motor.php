<?php

return [
    'reintento' => [
        'max_intentos' => 3,
        'intervalo_minutos' => 2,
    ],

    'periodos' => [
        'mensual' => 30,
        'anual' => 35,
    ],

    'cobro' => [
        'timeout_min' => 2,
    ],

    'pasarela' => [
        'probabilidades' => [
            'aprobado' => 60,
            'rechazado' => 30,
            'timeout' => 10,
        ],
    ],
];
