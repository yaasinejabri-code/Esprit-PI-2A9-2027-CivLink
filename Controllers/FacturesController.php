<?php
require_once __DIR__ . '/Controller.php';

class FacturesController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $error = '';
        $invoices = $users = $payments = [];
        $edit = null;
        try {
            $db = authDb();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!authCheck()) throw new RuntimeException('Session expirée. Rechargez la page.');
                $action = authPost('action');
                $id = (int)authPost('id');
                if ($action === 'invoice_add' || $action === 'invoice_edit') {
                    $userId = (int)authPost('idUtilisateur');
                    $reference = trim(authPost('referenceFacture'));
                    $provider = authPost('fournisseur');
                    $label = trim(authPost('libelle'));
                    $period = trim(authPost('periode'));
                    $due = authPost('dateEcheance');
                    $amount = trim(authPost('montant'));
                    $dueObj = DateTimeImmutable::createFromFormat('!Y-m-d', $due);
                    if ($userId < 1 || $reference === '' || mb_strlen($reference)>65 ||
                        !in_array($provider, ['STEG','SONEDE'], true) || $label === '' || mb_strlen($label)>160 ||
                        $period === '' || mb_strlen($period)>120 || !$dueObj || $dueObj->format('Y-m-d') !== $due ||
                        !preg_match('/^\d{1,9}(?:\.\d{1,3})?$/D', $amount) || (float)$amount <= 0) {
                        throw new RuntimeException('Vérifiez le citoyen, la référence, le fournisseur, la période, la date et le montant (DT).');
                    }
                    $q = $db->prepare('SELECT COUNT(*) FROM utilisateur WHERE idUtilisateur=? AND actif=1');
                    $q->execute([$userId]);
                    if (!(int)$q->fetchColumn()) throw new RuntimeException('Choisissez un utilisateur actif.');
                    if ($action === 'invoice_add') {
                        $q = $db->prepare('INSERT INTO facture(idUtilisateur,referenceFacture,fournisseur,libelle,periode,dateEcheance,montant) VALUES(?,?,?,?,?,?,?)');
                        $q->execute([$userId,$reference,$provider,$label,$period,$due,$amount]);
                    } else {
                        if ($id < 1) throw new RuntimeException('Facture inconnue.');
                        $q = $db->prepare('UPDATE facture SET idUtilisateur=?,referenceFacture=?,fournisseur=?,libelle=?,periode=?,dateEcheance=?,montant=? WHERE idFacture=? AND NOT EXISTS (SELECT 1 FROM paiement WHERE idFacture=?)');
                        $q->execute([$userId,$reference,$provider,$label,$period,$due,$amount,$id,$id]);
                        if (!$q->rowCount()) {
                            $s=$db->prepare('SELECT COUNT(*) FROM paiement WHERE idFacture=?'); $s->execute([$id]);
                            if ($s->fetchColumn()) throw new RuntimeException('Une facture réglée ne peut pas être modifiée.');
                        }
                    }
                } elseif ($action === 'invoice_delete') {
                    if ($id < 1) throw new RuntimeException('Facture inconnue.');
                    $q=$db->prepare('DELETE FROM facture WHERE idFacture=? AND NOT EXISTS(SELECT 1 FROM paiement WHERE idFacture=?)');
                    $q->execute([$id,$id]);
                    if (!$q->rowCount()) throw new RuntimeException('Facture réglée ou inexistante : suppression impossible.');
                } elseif ($action === 'invoice_pay') {
                    if ($id < 1) throw new RuntimeException('Facture inconnue.');
                    $db->beginTransaction();
                    $q=$db->prepare('SELECT montant FROM facture WHERE idFacture=? FOR UPDATE');$q->execute([$id]);
                    $amount=$q->fetchColumn();
                    if ($amount===false) throw new RuntimeException('Facture inexistante.');
                    $q=$db->prepare('INSERT INTO paiement(idFacture,montant,modePaiement,referencePaiement) VALUES(?,?,?,?)');
                    $q->execute([$id,$amount,'simulation-admin','SIM-'.bin2hex(random_bytes(12))]);
                    $db->commit();
                } else {
                    throw new RuntimeException('Action inconnue.');
                }
                header('Location: index.php?page=factures&ok=1',true,303);
                exit;
            }
            $users=$db->query('SELECT idUtilisateur,nom,prenom,email FROM utilisateur WHERE actif=1 ORDER BY nom,prenom')->fetchAll(PDO::FETCH_ASSOC);
            $invoices=$db->query('SELECT f.*,u.nom,u.prenom,u.email,p.idPaiement,p.datePaiement,p.referencePaiement FROM facture f JOIN utilisateur u ON u.idUtilisateur=f.idUtilisateur LEFT JOIN paiement p ON p.idFacture=f.idFacture ORDER BY f.idFacture DESC')->fetchAll(PDO::FETCH_ASSOC);
            $payments=array_values(array_filter($invoices,static fn($f)=>$f['idPaiement']!==null));
            if (isset($_GET['edit'])) foreach ($invoices as $f) if ((int)$f['idFacture']===(int)$_GET['edit'] && $f['idPaiement']===null) {$edit=$f;break;}
        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            $error=$e->getCode()==='23000' ? 'Référence déjà utilisée ou facture déjà réglée. Impossible de modifier cette opération.' : 'Erreur MySQL : importez database_factures.sql dans civlink_auth.';
        } catch (RuntimeException $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            $error=$e->getMessage();
        }
        $this->render('factures', compact('error','users','invoices','payments','edit'), 'Factures et paiements', 'factures');
    }
}
