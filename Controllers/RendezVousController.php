<?php
require_once __DIR__ . '/Controller.php';
if (!function_exists('be')) {
    function be($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
class RendezVousController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $authorized=true; $base='/CivLink/Views/FrontOffice';
$error='';$slots=[];$bookings=[];$edit=null;
if(true){try{
 $db=authDb();
 if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!authCheck())throw new RuntimeException('Formulaire expiré. Rechargez la page.');
  $action=authPost('action');$id=(int)authPost('id');
  if($action==='slot_add'||$action==='slot_edit'){
   $service=trim(authPost('serviceNom'));$lieu=trim(authPost('lieu'));$date=authPost('dateCreneau');$start=authPost('heureDebut');$end=authPost('heureFin');$active=authPost('actif')==='1'?1:0;
   $d=DateTimeImmutable::createFromFormat('!Y-m-d',$date);
   if($service===''||mb_strlen($service)>120||$lieu===''||mb_strlen($lieu)>180||!$d||$d->format('Y-m-d')!==$date||$date<date('Y-m-d')||!preg_match('/^\d{2}:\d{2}$/',$start)||!preg_match('/^\d{2}:\d{2}$/',$end)||$end<=$start)throw new RuntimeException('Vérifiez le service, lieu, date et horaires.');
   if($action==='slot_add'){$q=$db->prepare('INSERT INTO creneau_horaire(serviceNom,lieu,dateCreneau,heureDebut,heureFin,actif) VALUES(?,?,?,?,?,?)');$q->execute([$service,$lieu,$date,$start,$end,$active]);}
   else{
    $q=$db->prepare("SELECT COUNT(*) FROM rendez_vous WHERE idCreneau=? AND statut='confirme'");$q->execute([$id]);
    if((int)$q->fetchColumn()>0)throw new RuntimeException('Impossible de modifier un créneau déjà réservé : annulez d’abord la réservation.');
    $q=$db->prepare('UPDATE creneau_horaire SET serviceNom=?,lieu=?,dateCreneau=?,heureDebut=?,heureFin=?,actif=? WHERE idCreneau=?');$q->execute([$service,$lieu,$date,$start,$end,$active,$id]);
   }
  }elseif($action==='slot_delete'){
   $q=$db->prepare('DELETE FROM creneau_horaire WHERE idCreneau=?');$q->execute([$id]);
  }elseif($action==='booking_cancel'){
   $q=$db->prepare("UPDATE rendez_vous SET statut='annule' WHERE idRendezVous=? AND statut='confirme'");$q->execute([$id]);
  }elseif($action==='booking_delete'){
   $q=$db->prepare("DELETE FROM rendez_vous WHERE idRendezVous=? AND statut='annule'");$q->execute([$id]);
  }else throw new RuntimeException('Action inconnue.');
  header('Location: index.php?page=rendezvous&ok=1',true,303);exit;
 }
 $slots=$db->query("SELECT c.*,(SELECT COUNT(*) FROM rendez_vous r WHERE r.idCreneau=c.idCreneau AND r.statut='confirme') AS reserve FROM creneau_horaire c ORDER BY c.dateCreneau DESC,c.heureDebut DESC LIMIT 300")->fetchAll(PDO::FETCH_ASSOC);
 $bookings=$db->query("SELECT r.*,c.serviceNom,c.lieu,c.dateCreneau,c.heureDebut,u.nom,u.prenom,u.email FROM rendez_vous r JOIN creneau_horaire c ON c.idCreneau=r.idCreneau JOIN utilisateur u ON u.idUtilisateur=r.idUtilisateur ORDER BY r.idRendezVous DESC LIMIT 300")->fetchAll(PDO::FETCH_ASSOC);
 foreach($slots as $s)if(isset($_GET['edit'])&&(int)$_GET['edit']===(int)$s['idCreneau'])$edit=$s;
 }catch(PDOException $e){$error=$e->getCode()==='23000'?'Suppression impossible : créneau utilisé dans l’historique, ou créneau déjà existant.':'Erreur MySQL. Importez database_rendezvous.sql.';}
 catch(RuntimeException $e){$error=$e->getMessage();}
}

        $this->render('rendezvous', compact('error','slots','bookings','edit','authorized','base'), 'Gestion des rendez-vous', 'rendezvous');
    }
}
