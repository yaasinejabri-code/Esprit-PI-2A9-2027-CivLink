<?php
/**
 * Controllers/Controller.php
 * Controller de base du back-office :
 *  - vérifie que l'utilisateur est un administrateur (à CHAQUE requête)
 *  - capture le contenu d'une page puis l'insère dans layout.php
 */
require_once __DIR__ . '/../Models/Auth.php';

abstract class Controller
{
    /**
     * Bloque l'accès si l'utilisateur n'est pas un administrateur actif.
     * Le rôle est relu en base à chaque requête (pas seulement à la connexion).
     */
    protected function requireAdmin(): void
    {
        authStart();

        // 1. Pas connecté -> page de connexion du front-office
        if (empty($_SESSION['user']['id'])) {
            header('Location: /CivLink/Views/FrontOffice/pages/authentification.php');
            exit;
        }

        // 2. Connecté : on vérifie le rôle réel dans la base de données
        $stmt = authDb()->prepare(
            'SELECT r.nomRole, u.actif
             FROM utilisateur u
             JOIN role r ON r.idRole = u.idRole
             WHERE u.idUtilisateur = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => (int) $_SESSION['user']['id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !$row['actif'] || $row['nomRole'] !== 'administrateur') {
            http_response_code(403);
            die('Accès refusé : cet espace est réservé aux administrateurs.');
        }
    }

    /**
     * Fabrique le HTML de la vue $view, le met dans $content, puis charge le layout.
     */
    protected function render(string $view, array $data = [], string $title = 'Back Office', string $currentPage = ''): void
    {
        extract($data, EXTR_SKIP);

        if (!defined('CIVLINK_BACKOFFICE_RENDER')) define('CIVLINK_BACKOFFICE_RENDER', true);
        ob_start();
        require __DIR__ . '/../Views/BackOffice/pages/' . $view . '.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/BackOffice/layout.php';
    }
}