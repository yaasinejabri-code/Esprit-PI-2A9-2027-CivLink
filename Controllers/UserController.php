<?php
/**
 * Controllers/UserController.php
 * Gestion des utilisateurs (réservé aux administrateurs).
 */
require_once __DIR__ . '/Controller.php';

class UserController extends Controller
{
    private const USERS_URL = '/CivLink/Views/BackOffice/index.php?page=users';

    // ---------- Liste ----------
    public function index(): void
    {
        $this->requireAdmin();

        $users = authDb()->query(
            'SELECT u.idUtilisateur, u.nom, u.prenom, u.email, r.nomRole, u.actif, u.dateCreation
             FROM utilisateur u
             JOIN role r ON r.idRole = u.idRole
             ORDER BY u.idUtilisateur ASC'
        )->fetchAll(PDO::FETCH_ASSOC);

        // Message à afficher une seule fois (ex : "Rôle modifié")
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $csrf      = authToken();
        $currentId = (int) $_SESSION['user']['id'];

        $this->render(
            'users',
            compact('users', 'flash', 'csrf', 'currentId'),
            'Utilisateurs',
            'users'
        );
    }

    // ---------- Activer / désactiver un compte ----------
    public function toggleActive(): void
    {
        $this->requireAdmin();
        $id = $this->checkPostAndTarget();

        $stmt = authDb()->prepare('UPDATE utilisateur SET actif = NOT actif WHERE idUtilisateur = :id');
        $stmt->execute(['id' => $id]);

        $this->backToList('success', 'Statut du compte mis à jour.');
    }

    // ---------- Changer le rôle ----------
    public function changeRole(): void
    {
        $this->requireAdmin();
        $id   = $this->checkPostAndTarget();
        $role = authPost('role');

        // Liste blanche : on n'accepte que les rôles qui existent
        if (!in_array($role, ['citoyen', 'administrateur'], true)) {
            $this->backToList('error', 'Rôle invalide.');
        }

        $stmt = authDb()->prepare(
            'UPDATE utilisateur
             SET idRole = (SELECT idRole FROM role WHERE nomRole = :role)
             WHERE idUtilisateur = :id'
        );
        $stmt->execute(['role' => $role, 'id' => $id]);

        $this->backToList('success', 'Rôle mis à jour.');
    }

    // ---------- Outils internes ----------

    /** Vérifie POST + jeton CSRF + que l'admin ne cible pas son propre compte. Retourne l'id cible. */
    private function checkPostAndTarget(): int
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Méthode non autorisée.');
        }
        if (!authCheck()) {
            http_response_code(403);
            die('Jeton de sécurité invalide. Rechargez la page et réessayez.');
        }

        $id = (int) authPost('id');
        if ($id <= 0) {
            $this->backToList('error', 'Utilisateur invalide.');
        }
        if ($id === (int) $_SESSION['user']['id']) {
            $this->backToList('error', 'Vous ne pouvez pas modifier votre propre compte.');
        }
        return $id;
    }

    private function backToList(string $type, string $text): never
    {
        $_SESSION['flash'] = ['type' => $type, 'text' => $text];
        header('Location: ' . self::USERS_URL, true, 303);
        exit;
    }
}