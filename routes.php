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
    'rendez-vous'  => ['RendezVousController', 'index'],
    'ajouter-creneau' => ['RendezVousController', 'ajouterCreneau'],
    'supprimer-creneau' => ['RendezVousController', 'supprimerCreneau'],
    'supprimer-tous-creneaux' => ['RendezVousController', 'supprimerTousCreneaux'],
    'confirmer-rdv' => ['RendezVousController', 'confirmerRdv'],
    'annuler-rdv' => ['RendezVousController', 'annulerRdv'],
    ];