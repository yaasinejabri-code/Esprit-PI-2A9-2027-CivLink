<?php
// clé de page => [Controller, méthode]
return [
    'front' => [
        'home'        => ['Front\HomeController', 'index'],
        'signalements'=> ['Front\SignalementController', 'index'],
        // TODO: administrations, demarches, factures, faq, login
    ],
    'back' => [
        'dashboard'   => ['Back\DashboardController', 'index'],
        'signalements'=> ['Back\SignalementController', 'index'],
        // TODO: users, administrations, demarches, factures, faq
    ],
];
