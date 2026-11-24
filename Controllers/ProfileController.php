<?php
/**
 * Controllers/ProfileController.php
 * Page de profil de l'administrateur connecté.
 */
require_once __DIR__ . '/Controller.php';

class ProfileController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        $stmt = authDb()->prepare(
            'SELECT u.idUtilisateur, u.nom, u.prenom, u.email, r.nomRole, u.dateCreation
             FROM utilisateur u
             JOIN role r ON r.idRole = u.idRole
             WHERE u.idUtilisateur = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => (int) $_SESSION['user']['id']]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->render('profile', compact('profile'), 'Mon profil', 'profile');
    }
}