<?php
/**
 * Controllers/UserController.php
 * Gestion complète des utilisateurs.
 * Réservé aux administrateurs.
 */

require_once __DIR__ . '/Controller.php';

class UserController extends Controller
{
    private const USERS_URL = '/CivLink/Views/BackOffice/index.php?page=users';

    // ---------- Liste des utilisateurs ----------
    public function index(): void
    {
        $this->requireAdmin();

        $users = authDb()->query(
            'SELECT u.idUtilisateur, u.nom, u.prenom, u.email,
                    r.nomRole, u.actif, u.dateCreation
             FROM utilisateur u
             JOIN role r ON r.idRole = u.idRole
             ORDER BY u.idUtilisateur ASC'
        )->fetchAll(PDO::FETCH_ASSOC);

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $csrf = authToken();
        $currentId = (int) $_SESSION['user']['id'];

        $this->render(
            'users',
            compact('users', 'flash', 'csrf', 'currentId'),
            'Utilisateurs',
            'users'
        );
    }

    // ---------- Ajouter un utilisateur ----------
    public function create(): void
    {
        $this->requireAdmin();
        $this->checkPost();

        $nom = trim(authPost('nom'));
        $prenom = trim(authPost('prenom'));
        $email = strtolower(trim(authPost('email')));
        $password = authPost('password');
        $role = authPost('role');

        if (
            $nom === '' || $prenom === '' ||
            mb_strlen($nom) > 80 ||
            mb_strlen($prenom) > 80 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            strlen($email) > 254 ||
            strlen($password) < 8 ||
            strlen($password) > 72 ||
            !in_array($role, ['citoyen', 'administrateur'], true)
        ) {
            $this->backToList(
                'error',
                'Vérifiez les champs. Mot de passe : 8 à 72 caractères.'
            );
        }

        $db = authDb();

        try {
            $roleStmt = $db->prepare(
                'SELECT idRole FROM role WHERE nomRole = :role'
            );
            $roleStmt->execute(['role' => $role]);
            $roleId = $roleStmt->fetchColumn();

            if (!$roleId) {
                $this->backToList('error', 'Rôle introuvable.');
            }

            $stmt = $db->prepare(
                'INSERT INTO utilisateur
                (nom, prenom, email, motDePasseHash, idRole)
                VALUES (:nom, :prenom, :email, :password, :role)'
            );

            $stmt->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $roleId
            ]);

        } catch (PDOException $e) {
            $this->backToList(
                'error',
                $e->getCode() === '23000'
                    ? 'Cette adresse e-mail est déjà utilisée.'
                    : 'Erreur lors de la création.'
            );
        }

        $this->backToList('success', 'Utilisateur ajouté avec succès.');
    }

    // ---------- Modifier un utilisateur ----------
    public function update(): void
    {
        $this->requireAdmin();
        $id = $this->checkPostAndTarget();

        $nom = trim(authPost('nom'));
        $prenom = trim(authPost('prenom'));
        $email = strtolower(trim(authPost('email')));

        if (
            $nom === '' || $prenom === '' ||
            mb_strlen($nom) > 80 ||
            mb_strlen($prenom) > 80 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            strlen($email) > 254
        ) {
            $this->backToList(
                'error',
                'Vérifiez le nom, le prénom et l’adresse e-mail.'
            );
        }

        try {
            $stmt = authDb()->prepare(
                'UPDATE utilisateur
                 SET nom = :nom,
                     prenom = :prenom,
                     email = :email
                 WHERE idUtilisateur = :id'
            );

            $stmt->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'id' => $id
            ]);

        } catch (PDOException $e) {
            $this->backToList(
                'error',
                $e->getCode() === '23000'
                    ? 'Cette adresse e-mail est déjà utilisée.'
                    : 'Erreur lors de la modification.'
            );
        }

        $this->backToList('success', 'Utilisateur modifié avec succès.');
    }

    // ---------- Supprimer un utilisateur ----------
    public function delete(): void
    {
        $this->requireAdmin();
        $id = $this->checkPostAndTarget();

        try {
            $stmt = authDb()->prepare(
                'DELETE FROM utilisateur
                 WHERE idUtilisateur = :id'
            );

            $stmt->execute(['id' => $id]);

        } catch (PDOException $e) {
            $this->backToList(
                'error',
                'Suppression impossible : cet utilisateur possède des données liées. Désactivez son compte.'
            );
        }

        $this->backToList('success', 'Utilisateur supprimé avec succès.');
    }

    // ---------- Activer / désactiver ----------
    public function toggleActive(): void
    {
        $this->requireAdmin();
        $id = $this->checkPostAndTarget();

        $stmt = authDb()->prepare(
            'UPDATE utilisateur
             SET actif = NOT actif
             WHERE idUtilisateur = :id'
        );

        $stmt->execute(['id' => $id]);

        $this->backToList('success', 'Statut du compte mis à jour.');
    }

    // ---------- Changer le rôle ----------
    public function changeRole(): void
    {
        $this->requireAdmin();

        $id = $this->checkPostAndTarget();
        $role = authPost('role');

        if (!in_array($role, ['citoyen', 'administrateur'], true)) {
            $this->backToList('error', 'Rôle invalide.');
        }

        $stmt = authDb()->prepare(
            'UPDATE utilisateur
             SET idRole = (
                 SELECT idRole FROM role WHERE nomRole = :role
             )
             WHERE idUtilisateur = :id'
        );

        $stmt->execute([
            'role' => $role,
            'id' => $id
        ]);

        $this->backToList('success', 'Rôle mis à jour.');
    }

    // ---------- Vérification POST et CSRF ----------
    private function checkPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Méthode non autorisée.');
        }

        if (!authCheck()) {
            http_response_code(403);
            die('Jeton de sécurité invalide. Rechargez la page.');
        }
    }

    // ---------- Vérification de la cible ----------
    private function checkPostAndTarget(): int
    {
        $this->checkPost();

        $id = (int) authPost('id');

        if ($id <= 0) {
            $this->backToList('error', 'Utilisateur invalide.');
        }

        if ($id === (int) $_SESSION['user']['id']) {
            $this->backToList(
                'error',
                'Vous ne pouvez pas modifier ou supprimer votre propre compte.'
            );
        }

        return $id;
    }

    // ---------- Redirection avec message ----------
    private function backToList(string $type, string $text): never
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'text' => $text
        ];

        header('Location: ' . self::USERS_URL, true, 303);
        exit;
    }
}