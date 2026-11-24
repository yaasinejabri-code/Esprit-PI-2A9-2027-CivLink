<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/CivData.php';

class AdministrationsController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $error = '';
        $admins = $services = [];
        $edit = $editService = null;
        $types = CivData::types();
        $action = '';

        try {
            $db = authDb();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!authCheck()) {
                    throw new RuntimeException('Session expirée. Rechargez la page.');
                }
                $action = authPost('action');
                $id = (int) authPost('id');

                switch ($action) {
                    case 'admin_add':
                    case 'admin_edit':
                        $this->saveAdministration($db, $action, $id, $types);
                        break;
                    case 'admin_delete':
                        $this->deleteAdministration($db, $id);
                        break;
                    case 'service_add':
                    case 'service_edit':
                        $this->saveService($db, $action, $id);
                        break;
                    case 'service_delete':
                        if ($id < 1) {
                            throw new RuntimeException('Service inconnu.');
                        }
                        $db->prepare('DELETE FROM service WHERE idService = ?')->execute([$id]);
                        break;
                    default:
                        throw new RuntimeException('Action inconnue.');
                }
                header('Location: index.php?page=administrations&ok=1', true, 303);
                exit;
            }
        } catch (PDOException $e) {
            $error = $this->dbError($e, $action);
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }

        try {
            $db = authDb();
            $admins = $db->query('SELECT * FROM administration ORDER BY nom')->fetchAll(PDO::FETCH_ASSOC);
            $services = $db->query(
                'SELECT s.*, a.nom AS administrationNom
                 FROM service s
                 JOIN administration a ON a.idAdministration = s.idAdministration
                 ORDER BY a.nom, s.nom'
            )->fetchAll(PDO::FETCH_ASSOC);

            $editId = (int) ($_GET['edit'] ?? 0);
            $editServiceId = (int) ($_GET['editservice'] ?? 0);
            foreach ($admins as $a) {
                if ($editId && (int) $a['idAdministration'] === $editId) {
                    $edit = $a;
                    break;
                }
            }
            foreach ($services as $s) {
                if ($editServiceId && (int) $s['idService'] === $editServiceId) {
                    $editService = $s;
                    break;
                }
            }
        } catch (PDOException $e) {
            $error = $error ?: $this->dbError($e, '');
        }

        $this->render(
            'administrations',
            compact('error', 'admins', 'services', 'edit', 'editService', 'types'),
            'Gestion des administrations',
            'administrations'
        );
    }

    private function dbError(PDOException $e, string $action): string
    {
        if ($e->getCode() !== '23000') {
            return 'Erreur MySQL. Importez database_administrations.sql dans civlink_auth.';
        }
        if ($action === 'admin_delete') {
            return 'Suppression impossible : des données (services ou démarches) sont rattachées à cette administration. Vous pouvez la désactiver.';
        }
        return 'Cette opération est impossible : entrée déjà utilisée ou identifiant en conflit.';
    }

    private function saveAdministration(PDO $db, string $action, int $id, array $types): void
    {
        $nom = trim(authPost('nom'));
        $type = authPost('typeAdministration');
        $adresse = trim(authPost('adresse'));
        $horaires = trim(authPost('horaires'));
        $tel = trim(authPost('telephone'));
        $lat = trim(authPost('latitude'));
        $lng = trim(authPost('longitude'));
        $actif = authPost('actif') === '1' ? 1 : 0;

        $valid = isset($types[$type])
            && $nom !== '' && mb_strlen($nom) <= 160
            && $adresse !== '' && mb_strlen($adresse) <= 220
            && mb_strlen($horaires) <= 120
            && mb_strlen($tel) <= 40
            && is_numeric($lat) && (float) $lat >= -90 && (float) $lat <= 90
            && is_numeric($lng) && (float) $lng >= -180 && (float) $lng <= 180;
        if (!$valid) {
            throw new RuntimeException('Vérifiez le type, le nom, l’adresse et les coordonnées GPS.');
        }

        if ($action === 'admin_add') {
            $db->prepare(
                'INSERT INTO administration (siteRef, typeAdministration, nom, adresse, horaires, telephone, latitude, longitude, actif)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute(['a' . bin2hex(random_bytes(8)), $type, $nom, $adresse, $horaires, $tel, $lat, $lng, $actif]);
            return;
        }
        if ($id < 1) {
            throw new RuntimeException('Administration inconnue.');
        }
        $db->prepare(
            'UPDATE administration
             SET typeAdministration = ?, nom = ?, adresse = ?, horaires = ?, telephone = ?, latitude = ?, longitude = ?, actif = ?
             WHERE idAdministration = ?'
        )->execute([$type, $nom, $adresse, $horaires, $tel, $lat, $lng, $actif, $id]);
    }

    private function deleteAdministration(PDO $db, int $id): void
    {
        if ($id < 1) {
            throw new RuntimeException('Administration inconnue.');
        }
        $q = $db->prepare('SELECT COUNT(*) FROM service WHERE idAdministration = ?');
        $q->execute([$id]);
        $nb = (int) $q->fetchColumn();
        if ($nb > 0) {
            throw new RuntimeException(
                'Suppression impossible : cette administration possède ' . $nb . ' service(s). '
                . 'Supprimez-les ou rattachez-les à une autre administration, ou désactivez l’administration.'
            );
        }
        $db->prepare('DELETE FROM administration WHERE idAdministration = ?')->execute([$id]);
    }

    private function saveService(PDO $db, string $action, int $id): void
    {
        $adminId = (int) authPost('idAdministration');
        $nom = trim(authPost('nomService'));
        $desc = trim(authPost('description'));
        $actif = authPost('actif') === '1' ? 1 : 0;

        if ($adminId < 1 || $nom === '' || mb_strlen($nom) > 120 || mb_strlen($desc) > 2000) {
            throw new RuntimeException('Vérifiez les données du service.');
        }
        $q = $db->prepare('SELECT COUNT(*) FROM administration WHERE idAdministration = ?');
        $q->execute([$adminId]);
        if (!(int) $q->fetchColumn()) {
            throw new RuntimeException('Sélectionnez une administration existante.');
        }

        if ($action === 'service_add') {
            $db->prepare('INSERT INTO service (idAdministration, nom, description, actif) VALUES (?, ?, ?, ?)')
                ->execute([$adminId, $nom, $desc, $actif]);
            return;
        }
        if ($id < 1) {
            throw new RuntimeException('Service inconnu.');
        }
        $db->prepare('UPDATE service SET idAdministration = ?, nom = ?, description = ?, actif = ? WHERE idService = ?')
            ->execute([$adminId, $nom, $desc, $actif, $id]);
    }
}
