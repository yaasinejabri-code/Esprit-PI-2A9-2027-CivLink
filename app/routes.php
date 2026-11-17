<?php
return [
    'front' => [
        'home'        => ['Front\HomeController', 'index'],
        'signalements' => ['Front\SignalementController', 'index'],
    ],
    'back' => [
        'dashboard'    => ['Back\DashboardController', 'index'],
        'signalements' => ['Back\SignalementController', 'index'],
    ],
];