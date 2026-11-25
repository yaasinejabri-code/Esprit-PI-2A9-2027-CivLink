<?php
/**
 * Controllers/RendezVousController.php
 * Gestion des rendez-vous et des créneaux (réservé aux administrateurs).
 */
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/CivData.php';

class RendezVousController extends Controller
{
    private const RDV_URL = '/CivLink/Views/BackOffice/index.php?page=rendez-vous';

    // ---------- Affichage de la page (créneaux + liste des RDV) ----------
    public function index(): void
    {
        $this->requireAdmin();

        // Liste des créneaux horaires
        $creneaux = authDb()->query(
            'SELECT idCreneau, typeService, dateRendezVous, heureDebut, heureFin, disponibilite
             FROM creneau_horaire
             ORDER BY dateRendezVous DESC, heureDebut ASC'
        )->fetchAll(PDO::FETCH_ASSOC);

        // Liste des rendez-vous avec le citoyen concerné
        $rdvs = authDb()->query(
            'SELECT r.idRdv, r.dateReservation, r.statut,
                    u.nom, u.prenom, u.email,
                    c.typeService, c.dateRendezVous, c.heureDebut, c.heureFin
             FROM rendez_vous r
             JOIN utilisateur u ON u.idUtilisateur = r.idUtilisateur
             JOIN creneau_horaire c ON c.idCreneau = r.idCreneau
             ORDER BY r.dateReservation DESC'
        )->fetchAll(PDO::FETCH_ASSOC);

        // Noms lisibles des services (depuis CivData)
        $types = CivData::types();

        // Message à afficher une seule fois
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $csrf = authToken();

        $this->render(
            'rendez-vous',
            compact('creneaux', 'rdvs', 'types', 'flash', 'csrf'),
            'Rendez-vous',
            'rendez-vous'
        );
    }

    // ---------- Ajouter un créneau ----------
    public function ajouterCreneau(): void
    {
        $this->requireAdmin();
        $this->checkPost();

        $typeService = authPost('typeService');
        $date        = authPost('dateRendezVous');
        $heureDebut  = authPost('heureDebut');
        $heureFin    = authPost('heureFin');

        // Vérifications simples
        if ($typeService === '' || $date === '' || $heureDebut === '' || $heureFin === '' || $heureFin <= $heureDebut) {
            $this->backToList('error', 'Vérifiez le service, la date et les heures du créneau.');
        }

        $stmt = authDb()->prepare(
            'INSERT INTO creneau_horaire (typeService, dateRendezVous, heureDebut, heureFin, disponibilite)
             VALUES (:typeService, :dateRendezVous, :heureDebut, :heureFin, 1)'
        );
        $stmt->execute([
            'typeService'    => $typeService,
            'dateRendezVous' => $date,
            'heureDebut'     => $heureDebut,
            'heureFin'       => $heureFin,
        ]);

        $this->backToList('success', 'Créneau ajouté.');
    }

    // ---------- Supprimer un créneau ----------
    public function supprimerCreneau(): void
    {
        $this->requireAdmin();
        $this->checkPost();

        $id = (int) authPost('idCreneau');
        if ($id <= 0) {
            $this->backToList('error', 'Créneau invalide.');
        }

        // On ne supprime pas un créneau avec des rendez-vous actifs (en attente ou confirmés)
        $stmt = authDb()->prepare(
            "SELECT COUNT(*) FROM rendez_vous WHERE idCreneau = :id AND statut <> 'annule'"
        );
        $stmt->execute(['id' => $id]);
        if ((int) $stmt->fetchColumn() > 0) {
            $this->backToList('error', 'Ce créneau est réservé par un rendez-vous actif.');
        }

        // On supprime d'abord les rendez-vous annulés liés à ce créneau (libère la contrainte)
        authDb()->prepare('DELETE FROM rendez_vous WHERE idCreneau = :id AND statut = :statut')
            ->execute(['id' => $id, 'statut' => 'annule']);

        // Puis on supprime le créneau
        $stmt = authDb()->prepare('DELETE FROM creneau_horaire WHERE idCreneau = :id');
        $stmt->execute(['id' => $id]);

        $this->backToList('success', 'Créneau supprimé.');
    }

    // ---------- Supprimer tous les créneaux d'un service ----------
    public function supprimerTousCreneaux(): void
    {
        $this->requireAdmin();
        $this->checkPost();

        $typeService = authPost('typeService');
        if ($typeService === '') {
            $this->backToList('error', 'Service invalide.');
        }

        // Supprimer les rendez-vous annulés sur les créneaux de ce service
        authDb()->prepare(
            "DELETE rv FROM rendez_vous rv
             JOIN creneau_horaire c ON c.idCreneau = rv.idCreneau
             WHERE c.typeService = :type AND rv.statut = 'annule'"
        )->execute(['type' => $typeService]);

        // Supprimer les créneaux de ce service (ceux sans RDV actif restent)
        $stmt = authDb()->prepare(
            "DELETE FROM creneau_horaire
             WHERE typeService = :type
             AND idCreneau NOT IN (
                 SELECT idCreneau FROM rendez_vous WHERE statut <> 'annule'
             )"
        );
        $stmt->execute(['type' => $typeService]);

        $this->backToList('success', 'Tous les créneaux du service ont été supprimés.');
    }

    // ---------- Confirmer un rendez-vous ----------
    public function confirmerRdv(): void
    {
        $this->requireAdmin();
        $this->checkPost();

        $id = (int) authPost('idRdv');
        if ($id <= 0) {
            $this->backToList('error', 'Rendez-vous invalide.');
        }

        $stmt = authDb()->prepare("UPDATE rendez_vous SET statut = 'confirme' WHERE idRdv = :id");
        $stmt->execute(['id' => $id]);

        $this->backToList('success', 'Rendez-vous confirmé.');
    }

    // ---------- Annuler un rendez-vous ----------
    public function annulerRdv(): void
    {
        $this->requireAdmin();
        $this->checkPost();

        $id = (int) authPost('idRdv');
        if ($id <= 0) {
            $this->backToList('error', 'Rendez-vous invalide.');
        }

        // On récupère le créneau pour le rendre de nouveau disponible
        $stmt = authDb()->prepare('SELECT idCreneau FROM rendez_vous WHERE idRdv = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $this->backToList('error', 'Rendez-vous introuvable.');
        }

        authDb()->prepare("UPDATE rendez_vous SET statut = 'annule' WHERE idRdv = :id")
            ->execute(['id' => $id]);

        // Le créneau redevient disponible
        authDb()->prepare('UPDATE creneau_horaire SET disponibilite = 1 WHERE idCreneau = :id')
            ->execute(['id' => $row['idCreneau']]);

        $this->backToList('success', 'Rendez-vous annulé.');
    }

    // ---------- Outils internes ----------

    /** Vérifie que la requête est bien un POST protégé par le jeton CSRF. */
    private function checkPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Méthode non autorisée.');
        }
        if (!authCheck()) {
            http_response_code(403);
            die('Jeton de sécurité invalide. Rechargez la page et réessayez.');
        }
    }

    private function backToList(string $type, string $text): never
    {
        $_SESSION['flash'] = ['type' => $type, 'text' => $text];
        header('Location: ' . self::RDV_URL, true, 303);
        exit;
    }
}
