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
        try {
            $db = authDb();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!authCheck()) throw new RuntimeException('Session expirée. Rechargez la page.');
                $action = authPost('action');
                $id = (int)authPost('id');
                if ($action === 'admin_add' || $action === 'admin_edit') {
                    $nom = trim(authPost('nom'));
                    $type = authPost('typeAdministration');
                    $adresse = trim(authPost('adresse'));
                    $horaires = trim(authPost('horaires'));
                    $tel = trim(authPost('telephone'));
                    $lat = trim(authPost('latitude'));
                    $lng = trim(authPost('longitude'));
                    $actif = authPost('actif') === '1' ? 1 : 0;
                    if (!isset($types[$type]) || $nom === '' || mb_strlen($nom)>160 || $adresse === '' || mb_strlen($adresse)>220 || mb_strlen($horaires)>120 || mb_strlen($tel)>40 || !is_numeric($lat) || !is_numeric($lng) || (float)$lat < -90 || (float)$lat > 90 || (float)$lng < -180 || (float)$lng > 180) {
                        throw new RuntimeException('Vérifiez le type, le nom, l’adresse et les coordonnées GPS.');
                    }
                    if ($action === 'admin_add') {
                        $ref = 'a' . bin2hex(random_bytes(8));
                        $q=$db->prepare('INSERT INTO administration(siteRef,typeAdministration,nom,adresse,horaires,telephone,latitude,longitude,actif) VALUES (?,?,?,?,?,?,?,?,?)');
                        $q->execute([$ref,$type,$nom,$adresse,$horaires,$tel,$lat,$lng,$actif]);
                    } else {
                        if ($id<1) throw new RuntimeException('Administration inconnue.');
                        $q=$db->prepare('UPDATE administration SET typeAdministration=?,nom=?,adresse=?,horaires=?,telephone=?,latitude=?,longitude=?,actif=? WHERE idAdministration=?');
                        $q->execute([$type,$nom,$adresse,$horaires,$tel,$lat,$lng,$actif,$id]);
                    }
                } elseif ($action === 'admin_delete') {
                    if ($id<1) throw new RuntimeException('Administration inconnue.');
                    $q=$db->prepare('DELETE FROM administration WHERE idAdministration=?'); $q->execute([$id]);
                } elseif ($action === 'service_add' || $action === 'service_edit') {
                    $adminId=(int)authPost('idAdministration');
                    $name=trim(authPost('nomService'));
                    $desc=trim(authPost('description'));
                    $actif=authPost('actif')==='1'?1:0;
                    if ($adminId<1 || $name==='' || mb_strlen($name)>120 || mb_strlen($desc)>2000) throw new RuntimeException('Vérifiez les données du service.');
                    $q=$db->prepare('SELECT COUNT(*) FROM administration WHERE idAdministration=?'); $q->execute([$adminId]);
                    if (!(int)$q->fetchColumn()) throw new RuntimeException('Sélectionnez une administration existante.');
                    if ($action==='service_add') {
                        $q=$db->prepare('INSERT INTO service(idAdministration,nom,description,actif) VALUES (?,?,?,?)'); $q->execute([$adminId,$name,$desc,$actif]);
                    } else {
                        if ($id<1) throw new RuntimeException('Service inconnu.');
                        $q=$db->prepare('UPDATE service SET idAdministration=?,nom=?,description=?,actif=? WHERE idService=?'); $q->execute([$adminId,$name,$desc,$actif,$id]);
                    }
                } elseif ($action === 'service_delete') {
                    if ($id<1) throw new RuntimeException('Service inconnu.');
                    $q=$db->prepare('DELETE FROM service WHERE idService=?'); $q->execute([$id]);
                } else {
                    throw new RuntimeException('Action inconnue.');
                }
                header('Location: index.php?page=administrations&ok=1',true,303); exit;
            }
            $admins=$db->query('SELECT * FROM administration ORDER BY nom')->fetchAll(PDO::FETCH_ASSOC);
            $services=$db->query('SELECT s.*,a.nom AS administrationNom FROM service s JOIN administration a ON a.idAdministration=s.idAdministration ORDER BY a.nom,s.nom')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($admins as $a) if (isset($_GET['edit']) && $a['idAdministration']===(int)$_GET['edit']) { $edit=$a; break; }
            foreach ($services as $s) if (isset($_GET['editservice']) && $s['idService']===(int)$_GET['editservice']) { $editService=$s; break; }
        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            $error=$e->getCode()==='23000'?'Cette opération est impossible : entrée déjà utilisée ou identifiant en conflit.':'Erreur MySQL. Importez database_administrations.sql dans civlink_auth.';
        } catch (RuntimeException $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            $error=$e->getMessage();
        }
        $this->render('administrations', compact('error','admins','services','edit','editService','types'), 'Gestion des administrations', 'administrations');
    }
}
