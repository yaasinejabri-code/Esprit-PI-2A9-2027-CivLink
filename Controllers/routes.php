<?php
/**
 * Controllers/routes.php
 * Table de routage du back-office : 'page' => ['NomDuController', 'méthode']
 * Pour activer une nouvelle page : créer son controller, puis ajouter une ligne ici.
 */
return [
    'dashboard' => ['DashboardController', 'index'],
    'users'     => ['UserController', 'index'],
    'users-role'   => ['UserController', 'changeRole'],
    'users-toggle' => ['UserController', 'toggleActive'],
    'profile'      => ['ProfileController', 'index'],
    'actualites' => ['ActualitesController', 'index'],
    'rendezvous' => ['RendezVousController', 'index'],
    'administrations' => ['AdministrationsController', 'index'],
    'demarches' => ['DemarchesController', 'index'],
    'documents' => ['DocumentRequisController', 'index'],
    'signalements' => ['SignalementsController', 'index'],
    'factures' => ['FacturesController', 'index'],
    ];