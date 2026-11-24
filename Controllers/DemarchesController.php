<?php
require_once __DIR__ . '/Controller.php';

class DemarchesController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $error = '';
        $admins = $docs = $demarches = [];
        $editDoc = $editDemarche = null;
        $selectedDocs = [];
        try {
            $db = authDb();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!authCheck()) throw new RuntimeException('Session expirée : rechargez la page.');
                $action = authPost('action');
                $id = (int)authPost('id');
                if (in_array($action, ['doc_add','doc_edit'], true)) {
                    $nom = trim(authPost('nom'));
                    $desc = trim(authPost('description'));
                    $actif = authPost('actif') === '1' ? 1 : 0;
                    if ($nom === '' || mb_strlen($nom)>180 || mb_strlen($desc)>5000) throw new RuntimeException('Nom de document invalide.');
                    if ($action === 'doc_add') {
                        $q=$db->prepare('INSERT INTO document_requis(nom,description,actif) VALUES(?,?,?)');
                        $q->execute([$nom,$desc,$actif]);
                    } else {
                        if ($id<1) throw new RuntimeException('Document inconnu.');
                        $q=$db->prepare('UPDATE document_requis SET nom=?, description=?, actif=? WHERE idDocument=?');
                        $q->execute([$nom,$desc,$actif,$id]);
                    }
                } elseif ($action === 'doc_delete') {
                    if ($id<1) throw new RuntimeException('Document inconnu.');
                    $q=$db->prepare('DELETE FROM document_requis WHERE idDocument=?'); $q->execute([$id]);
                } elseif (in_array($action, ['dem_add','dem_edit'], true)) {
                    $adminId=(int)authPost('idAdministration');
                    $titre=trim(authPost('titre'));
                    $description=trim(authPost('description'));
                    $delai=trim(authPost('delai'));
                    $cout=trim(authPost('cout'));
                    $etapes=trim(authPost('etapes'));
                    $actif=authPost('actif') === '1' ? 1 : 0;
                    $ids=array_unique(array_map('intval', is_array($_POST['documents']??null) ? $_POST['documents'] : []));
                    $ids=array_values(array_filter($ids,fn($x)=>$x>0));
                    if ($adminId<1 || $titre==='' || mb_strlen($titre)>180 || mb_strlen($description)>5000 || mb_strlen($delai)>120 || mb_strlen($cout)>120 || mb_strlen($etapes)>5000 || !$ids) {
                        throw new RuntimeException('Choisissez une administration, un titre et au moins un document requis.');
                    }
                    $q=$db->prepare('SELECT COUNT(*) FROM administration WHERE idAdministration=?'); $q->execute([$adminId]);
                    if (!(int)$q->fetchColumn()) throw new RuntimeException('Administration inexistante.');
                    $marks=implode(',',array_fill(0,count($ids),'?'));
                    $q=$db->prepare("SELECT COUNT(*) FROM document_requis WHERE idDocument IN ($marks)");$q->execute($ids);
                    if ((int)$q->fetchColumn()!==count($ids)) throw new RuntimeException('Un document sélectionné est inexistant.');
                    $db->beginTransaction();
                    if ($action === 'dem_add') {
                        $q=$db->prepare('INSERT INTO demarche(idAdministration,titre,description,delai,cout,etapes,actif) VALUES(?,?,?,?,?,?,?)');
                        $q->execute([$adminId,$titre,$description,$delai,$cout,$etapes,$actif]);
                        $id=(int)$db->lastInsertId();
                    } else {
                        if ($id<1) throw new RuntimeException('Démarche inconnue.');
                        $q=$db->prepare('UPDATE demarche SET idAdministration=?,titre=?,description=?,delai=?,cout=?,etapes=?,actif=? WHERE idDemarche=?');
                        $q->execute([$adminId,$titre,$description,$delai,$cout,$etapes,$actif,$id]);
                        $q=$db->prepare('DELETE FROM demarche_document WHERE idDemarche=?');$q->execute([$id]);
                    }
                    $q=$db->prepare('INSERT INTO demarche_document(idDemarche,idDocument) VALUES(?,?)');
                    foreach ($ids as $docId) $q->execute([$id,$docId]);
                    $db->commit();
                } elseif ($action === 'dem_delete') {
                    if ($id<1) throw new RuntimeException('Démarche inconnue.');
                    $q=$db->prepare('DELETE FROM demarche WHERE idDemarche=?');$q->execute([$id]);
                } else {
                    throw new RuntimeException('Action inconnue.');
                }
                header('Location: index.php?page=demarches&ok=1',true,303);exit;
            }
            $admins=$db->query('SELECT idAdministration, nom, typeAdministration, actif FROM administration ORDER BY nom')->fetchAll(PDO::FETCH_ASSOC);
            $docs=$db->query('SELECT * FROM document_requis ORDER BY nom')->fetchAll(PDO::FETCH_ASSOC);
            $demarches=$db->query('SELECT d.*, a.nom AS administrationNom FROM demarche d JOIN administration a ON a.idAdministration=d.idAdministration ORDER BY d.idDemarche DESC')->fetchAll(PDO::FETCH_ASSOC);
            $links=$db->query('SELECT idDemarche,idDocument FROM demarche_document')->fetchAll(PDO::FETCH_ASSOC);
            $linked=[];
            foreach ($links as $link) $linked[(int)$link['idDemarche']][]=(int)$link['idDocument'];
            foreach ($demarches as &$d) $d['documentIds']=$linked[(int)$d['idDemarche']]??[];
            unset($d);
            foreach ($docs as $doc) if (isset($_GET['editdoc']) && (int)$doc['idDocument']===(int)$_GET['editdoc']) { $editDoc=$doc; break; }
            foreach ($demarches as $d) if (isset($_GET['editdem']) && (int)$d['idDemarche']===(int)$_GET['editdem']) { $editDemarche=$d; $selectedDocs=$d['documentIds']; break; }
        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            $error=$e->getCode()==='23000'?'Impossible de supprimer cet élément : il est utilisé dans une autre fiche.':'Erreur MySQL. Importez database_demarches.sql après database_administrations.sql.';
        } catch (RuntimeException $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            $error=$e->getMessage();
        }
        $this->render('demarches_admin',compact('error','admins','docs','demarches','editDoc','editDemarche','selectedDocs'),'Démarches et documents','demarches');
    }
}
