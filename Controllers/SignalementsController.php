<?php
require_once __DIR__ . '/Controller.php';

class SignalementsController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $error = '';
        $categories = $reports = [];
        $editCategory = null;
        $statuses = ['En attente', 'En cours', 'Résolu', 'Annulé'];
        try {
            $db = authDb();
            // Private images: never expose a citizen's photo through an unauthenticated URL.
            if (isset($_GET['photo'])) {
                $photoId = filter_var($_GET['photo'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if (!$photoId) { http_response_code(404); exit; }
                $q = $db->prepare('SELECT photo, photoMime FROM signalement WHERE idSignalement=?');
                $q->execute([$photoId]);
                $photo = $q->fetch(PDO::FETCH_ASSOC);
                if (!$photo || !$photo['photo'] || !in_array($photo['photoMime'], ['image/jpeg','image/png','image/webp'], true)) {
                    http_response_code(404); exit;
                }
                header('Content-Type: ' . $photo['photoMime']);
                header('X-Content-Type-Options: nosniff');
                header('Cache-Control: private, no-store');
                echo $photo['photo'];
                exit;
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!authCheck()) throw new RuntimeException('Session expirée, actualisez la page.');
                $action = authPost('action');
                $id = (int)authPost('id');
                if (in_array($action, ['cat_add','cat_edit'], true)) {
                    $nom = trim(authPost('nom'));
                    if ($nom === '' || mb_strlen($nom) > 100) throw new RuntimeException('Le nom de la catégorie doit contenir entre 1 et 100 caractères.');
                    if ($action === 'cat_add') {
                        $q=$db->prepare('INSERT INTO categorie_signalement(nom) VALUES(?)');
                        $q->execute([$nom]);
                    } else {
                        if ($id < 1) throw new RuntimeException('Catégorie invalide.');
                        $q=$db->prepare('UPDATE categorie_signalement SET nom=? WHERE idCategorie=?');
                        $q->execute([$nom,$id]);
                    }
                } elseif ($action === 'cat_delete') {
                    if ($id < 1) throw new RuntimeException('Catégorie invalide.');
                    $q=$db->prepare('SELECT COUNT(*) FROM signalement WHERE idCategorie=?');
                    $q->execute([$id]);
                    if ((int)$q->fetchColumn() > 0) {
                        throw new RuntimeException('Cette catégorie contient des signalements. Modifiez-la au lieu de la supprimer.');
                    }
                    $q=$db->prepare('DELETE FROM categorie_signalement WHERE idCategorie=?');
                    $q->execute([$id]);
                } elseif ($action === 'report_status') {
                    $status = authPost('statut');
                    if ($id < 1 || !in_array($status, $statuses, true)) throw new RuntimeException('Statut invalide.');
                    $q=$db->prepare('UPDATE signalement SET statut=? WHERE idSignalement=? AND statut<>\'Annulé\'');
                    $q->execute([$status,$id]);
                    // A no-op update (same status) is valid; MySQL may report zero changed rows.
                } elseif ($action === 'report_delete') {
                    if ($id < 1) throw new RuntimeException('Signalement invalide.');
                    $q=$db->prepare('DELETE FROM signalement WHERE idSignalement=?');
                    $q->execute([$id]);
                } else {
                    throw new RuntimeException('Action inconnue.');
                }
                header('Location: index.php?page=signalements&ok=1',true,303);
                exit;
            }
            $categories = $db->query('SELECT c.idCategorie,c.nom,COUNT(s.idSignalement) AS total FROM categorie_signalement c LEFT JOIN signalement s ON s.idCategorie=c.idCategorie GROUP BY c.idCategorie,c.nom ORDER BY c.nom')->fetchAll(PDO::FETCH_ASSOC);
            $reports = $db->query('SELECT s.idSignalement,s.titre,s.description,s.adresse,s.latitude,s.longitude,s.statut,s.dateSignalement, (s.photo IS NOT NULL) AS hasPhoto, c.nom AS categorie, u.nom AS nomCitoyen,u.prenom,u.email FROM signalement s JOIN categorie_signalement c ON c.idCategorie=s.idCategorie JOIN utilisateur u ON u.idUtilisateur=s.idUtilisateur ORDER BY s.idSignalement DESC')->fetchAll(PDO::FETCH_ASSOC);
            if (isset($_GET['editcat'])) {
                foreach ($categories as $c) if ((int)$c['idCategorie'] === (int)$_GET['editcat']) { $editCategory=$c; break; }
            }
        } catch (PDOException $e) {
            $error = $e->getCode() === '23000' ? 'Ce nom existe déjà, ou cet élément est encore utilisé.' : 'Erreur MySQL : vérifiez que database_signalements.sql a été importé dans civlink_auth.';
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }
        $this->render('signalements', compact('error','categories','reports','editCategory','statuses'), 'Gestion des signalements', 'signalements');
    }
}
