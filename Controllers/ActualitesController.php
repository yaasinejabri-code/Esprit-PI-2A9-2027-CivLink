<?php
require_once __DIR__ . '/Controller.php';
if (!function_exists('eact')) {
    function eact($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('validLink')) {
    function validLink(string $s): bool { return $s === '' || (bool)preg_match('~^(?:administrations|demarches|signalements|factures|rendezvous|actualites)\.php$|^\.\./index\.php$~', $s); }
}
class ActualitesController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $authorized=true; $base='/CivLink/Views/FrontOffice'; $user=$_SESSION['user']??[]; $editCategory=null;
$err=''; $ok=''; $categories=[]; $articles=[]; $edit=null;
if(true){
 try {
  $db=authDb();
  if($_SERVER['REQUEST_METHOD']==='POST') {
   if(!authCheck()) throw new RuntimeException('Session expirée ou formulaire invalide. Rechargez la page.');
   $action=authPost('action'); $id=(int)authPost('id');
   if($action==='add_category' || $action==='edit_category') {
    $name=trim(authPost('nom')); $desc=trim(authPost('description'));
    if($name==='' || mb_strlen($name)>90 || mb_strlen($desc)>500) throw new RuntimeException('Catégorie invalide (90 caractères maximum).');
    if($action==='add_category'){$q=$db->prepare('INSERT INTO categorie_actualite(nom,description) VALUES(?,?)');$q->execute([$name,$desc]);}
    else {$q=$db->prepare('UPDATE categorie_actualite SET nom=?,description=? WHERE idCategorie=?');$q->execute([$name,$desc,$id]);}
   } elseif($action==='delete_category') {
    $q=$db->prepare('DELETE FROM categorie_actualite WHERE idCategorie=?');$q->execute([$id]);
   } elseif($action==='add_article' || $action==='edit_article') {
    $titre=trim(authPost('titre'));$resume=trim(authPost('resume'));$contenu=trim(authPost('contenu'));$category=(int)authPost('categorie');
    $status=authPost('statut');$icon=trim(authPost('icone'));$reading=trim(authPost('lecture'));$link=trim(authPost('lien'));$linkText=trim(authPost('lienTexte'));
    if(!$icon)$icon='far fa-newspaper';
    if(!$reading)$reading='2 min';
    if($titre===''||mb_strlen($titre)>180||$resume===''||mb_strlen($resume)>500||mb_strlen($contenu)<25||mb_strlen($contenu)>15000)throw new RuntimeException('Titre, résumé ou contenu invalide (contenu : 25 à 15000 caractères).');
    if(!in_array($status,['brouillon','publie'],true)||!validLink($link)||mb_strlen($linkText)>100||mb_strlen($icon)>90||mb_strlen($reading)>20)throw new RuntimeException('Vérifiez le statut et le lien interne.');
    $q=$db->prepare('SELECT COUNT(*) FROM categorie_actualite WHERE idCategorie=?');$q->execute([$category]);if(!(int)$q->fetchColumn())throw new RuntimeException('Sélectionnez une catégorie existante.');
    $params=[$category,$titre,$resume,$contenu,$icon,$reading,$status,$link?:null,$linkText?:null];
    if($action==='add_article'){
     $q=$db->prepare('INSERT INTO actualite(idCategorie,titre,resume,contenu,icone,lecture,statut,lien,lienTexte,datePublication,idAuteur) VALUES(?,?,?,?,?,?,?,?,?,NOW(),?)');$q->execute([...$params,(int)$user['id']]);
    }else{
     $q=$db->prepare('UPDATE actualite SET idCategorie=?,titre=?,resume=?,contenu=?,icone=?,lecture=?,statut=?,lien=?,lienTexte=?,datePublication=CASE WHEN statut=? AND ?=\'publie\' THEN NOW() ELSE datePublication END WHERE idActualite=?');
     $q->execute([...$params,'brouillon',$status,$id]);
    }
   } elseif($action==='delete_article') {
    $q=$db->prepare('DELETE FROM actualite WHERE idActualite=?');$q->execute([$id]);
   } else {throw new RuntimeException('Action inconnue.');}
   header('Location: index.php?page=actualites&ok=1',true,303);exit;
  }
  $categories=$db->query('SELECT * FROM categorie_actualite ORDER BY nom')->fetchAll(PDO::FETCH_ASSOC);
  $articles=$db->query('SELECT a.*,c.nom AS categorie FROM actualite a JOIN categorie_actualite c ON c.idCategorie=a.idCategorie ORDER BY a.idActualite DESC')->fetchAll(PDO::FETCH_ASSOC);
  if(isset($_GET['edit'])){foreach($articles as $a){if((int)$a['idActualite']===(int)$_GET['edit']){$edit=$a;break;}}}
  if(isset($_GET['editcat'])){foreach($categories as $c){if((int)$c['idCategorie']===(int)$_GET['editcat']){$editCategory=$c;break;}}}
  $ok=isset($_GET['ok'])?'Modification enregistrée.':'';
 }catch(PDOException $ex){$err=$ex->getCode()==='23000'?'Suppression impossible : catégorie utilisée par un article, ou nom déjà existant.':'Erreur MySQL. Vérifiez l’importation du fichier database_actualites.sql.';}
 catch(RuntimeException $ex){$err=$ex->getMessage();}
}

        $this->render('actualites', compact('err','ok','categories','articles','edit','editCategory','authorized','base'), 'Gestion des actualités', 'actualites');
    }
}
