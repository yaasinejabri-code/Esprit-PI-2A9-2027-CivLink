<?php
/**
 * Controllers/DashboardController.php
 * Tableau de bord : statistiques sur les utilisateurs.
 */
require_once __DIR__ . '/Controller.php';

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        $db = authDb();

        $total = (int) $db->query('SELECT COUNT(*) FROM utilisateur')->fetchColumn();

        $count = $db->prepare(
            'SELECT COUNT(*) FROM utilisateur u
             JOIN role r ON r.idRole = u.idRole
             WHERE r.nomRole = :role'
        );
        $count->execute(['role' => 'administrateur']);
        $totalAdmins = (int) $count->fetchColumn();

        $count->execute(['role' => 'citoyen']);
        $totalCitoyens = (int) $count->fetchColumn();

        $latest = $db->query(
            'SELECT u.idUtilisateur, u.nom, u.prenom, u.email, r.nomRole, u.dateCreation
             FROM utilisateur u
             JOIN role r ON r.idRole = u.idRole
             ORDER BY u.dateCreation DESC
             LIMIT 5'
        )->fetchAll(PDO::FETCH_ASSOC);

        $this->render(
            'dashboard',
            compact('total', 'totalAdmins', 'totalCitoyens', 'latest'),
            'Tableau de bord',
            'dashboard'
        );
    }
}