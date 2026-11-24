<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/CivData.php';
require_once __DIR__ . '/../Models/Administration.php';
require_once __DIR__ . '/../Models/Service.php';

class AdministrationsController extends Controller
{
    private PDO $db;

    public function index(): void
    {
        $this->requireAdmin();
        $error = '';
        $admins = $services = $adminNames = [];
        $edit = $editService = null;
        $types = CivData::types();
        $action = '';

        try {
            $this->db = authDb();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!authCheck()) {
                    throw new RuntimeException('Session expirée. Rechargez la page.');
                }
                $action = authPost('action');
                $id = (int) authPost('id');

                switch ($action) {
                    case 'admin_add':
                    case 'admin_edit':
                        $this->saveAdministration($action, $id, $types);
                        break;
                    case 'admin_delete':
                        $this->deleteAdministration($id);
                        break;
                    case 'service_add':
                    case 'service_edit':
                        $this->saveService($action, $id);
                        break;
                    case 'service_delete':
                        $this->deleteService($id);
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
            $this->db = authDb();
            $admins = $this->findAllAdministrations();
            $services = $this->findAllServices();
            foreach ($admins as $a) {
                $adminNames[$a->getIdAdministration()] = $a->getNom();
            }
            $editId = (int) ($_GET['edit'] ?? 0);
            $editServiceId = (int) ($_GET['editservice'] ?? 0);
            foreach ($admins as $a) {
                if ($editId && $a->getIdAdministration() === $editId) {
                    $edit = $a;
                    break;
                }
            }
            foreach ($services as $s) {
                if ($editServiceId && $s->getIdService() === $editServiceId) {
                    $editService = $s;
                    break;
                }
            }
        } catch (PDOException $e) {
            $error = $error ?: $this->dbError($e, '');
        }

        $csrfToken = authToken();
        $this->render(
            'administrations',
            compact('error', 'admins', 'services', 'adminNames', 'edit', 'editService', 'types', 'csrfToken'),
            'Gestion des administrations',
            'administrations'
        );
    }

    /** @return Administration[] */
    private function findAllAdministrations(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM administration ORDER BY nom');
        $stmt->execute();
        return array_map([Administration::class, 'fromArray'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /** @return Service[] */
    private function findAllServices(): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*
             FROM service s
             JOIN administration a ON a.idAdministration = s.idAdministration
             ORDER BY a.nom, s.nom'
        );
        $stmt->execute();
        return array_map([Service::class, 'fromArray'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function administrationExists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM administration WHERE idAdministration = ?');
        $stmt->execute([$id]);
        return (bool) $stmt->fetchColumn();
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

    private function saveAdministration(string $action, int $id, array $types): void
    {
        $lat = trim(authPost('latitude'));
        $lng = trim(authPost('longitude'));
        $a = new Administration();
        $a->setIdAdministration($id);
        $a->setTypeAdministration(authPost('typeAdministration'));
        $a->setNom(trim(authPost('nom')));
        $a->setAdresse(trim(authPost('adresse')));
        $a->setHoraires(trim(authPost('horaires')));
        $a->setTelephone(trim(authPost('telephone')));
        $a->setActif(authPost('actif') === '1');

        $valid = isset($types[$a->getTypeAdministration()])
            && $a->getNom() !== '' && mb_strlen($a->getNom()) <= 160
            && $a->getAdresse() !== '' && mb_strlen($a->getAdresse()) <= 220
            && mb_strlen($a->getHoraires()) <= 120
            && mb_strlen($a->getTelephone()) <= 40
            && is_numeric($lat) && (float) $lat >= -90 && (float) $lat <= 90
            && is_numeric($lng) && (float) $lng >= -180 && (float) $lng <= 180;
        if (!$valid) {
            throw new RuntimeException('Vérifiez le type, le nom, l’adresse et les coordonnées GPS.');
        }
        $a->setLatitude((float) $lat);
        $a->setLongitude((float) $lng);
        $values = [
            $a->getTypeAdministration(), $a->getNom(), $a->getAdresse(), $a->getHoraires(),
            $a->getTelephone(), $a->getLatitude(), $a->getLongitude(), (int) $a->isActif(),
        ];

        if ($action === 'admin_add') {
            $a->setSiteRef('a' . bin2hex(random_bytes(8)));
            $this->db->prepare(
                'INSERT INTO administration (typeAdministration, nom, adresse, horaires, telephone, latitude, longitude, actif, siteRef)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([...$values, $a->getSiteRef()]);
            return;
        }
        if ($id < 1) {
            throw new RuntimeException('Administration inconnue.');
        }
        $this->db->prepare(
            'UPDATE administration
             SET typeAdministration = ?, nom = ?, adresse = ?, horaires = ?, telephone = ?, latitude = ?, longitude = ?, actif = ?
             WHERE idAdministration = ?'
        )->execute([...$values, $a->getIdAdministration()]);
    }

    private function deleteAdministration(int $id): void
    {
        if ($id < 1) {
            throw new RuntimeException('Administration inconnue.');
        }
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM service WHERE idAdministration = ?');
        $stmt->execute([$id]);
        $nb = (int) $stmt->fetchColumn();
        if ($nb > 0) {
            throw new RuntimeException(
                'Suppression impossible : cette administration possède ' . $nb . ' service(s). '
                . 'Supprimez-les ou rattachez-les à une autre administration, ou désactivez l’administration.'
            );
        }
        $this->db->prepare('DELETE FROM administration WHERE idAdministration = ?')->execute([$id]);
    }

    private function saveService(string $action, int $id): void
    {
        $s = new Service();
        $s->setIdService($id);
        $s->setIdAdministration((int) authPost('idAdministration'));
        $s->setNom(trim(authPost('nomService')));
        $s->setDescription(trim(authPost('description')));
        $s->setActif(authPost('actif') === '1');

        if ($s->getIdAdministration() < 1 || $s->getNom() === '' || mb_strlen($s->getNom()) > 120 || mb_strlen($s->getDescription()) > 2000) {
            throw new RuntimeException('Vérifiez les données du service.');
        }
        if (!$this->administrationExists($s->getIdAdministration())) {
            throw new RuntimeException('Sélectionnez une administration existante.');
        }
        $values = [$s->getIdAdministration(), $s->getNom(), $s->getDescription(), (int) $s->isActif()];

        if ($action === 'service_add') {
            $this->db->prepare('INSERT INTO service (idAdministration, nom, description, actif) VALUES (?, ?, ?, ?)')
                ->execute($values);
            return;
        }
        if ($id < 1) {
            throw new RuntimeException('Service inconnu.');
        }
        $this->db->prepare('UPDATE service SET idAdministration = ?, nom = ?, description = ?, actif = ? WHERE idService = ?')
            ->execute([...$values, $s->getIdService()]);
    }

    private function deleteService(int $id): void
    {
        if ($id < 1) {
            throw new RuntimeException('Service inconnu.');
        }
        $this->db->prepare('DELETE FROM service WHERE idService = ?')->execute([$id]);
    }
}
