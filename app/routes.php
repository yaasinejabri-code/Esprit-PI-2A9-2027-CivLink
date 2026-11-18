<?php
return [
    'front' => [
        'home'                  => ['Front\HomeController', 'index'],
        'signalements'          => ['Front\SignalementController', 'index'],
        'signalement-nouveau'   => ['Front\SignalementController', 'create'],
        'signalement-modifier'  => ['Front\SignalementController', 'edit'],
        'signalement-supprimer' => ['Front\SignalementController', 'delete'],
    ],
    'back' => [
        'dashboard'    => ['Back\DashboardController', 'index'],
        'signalements' => ['Back\SignalementController', 'index'],
    ],
];