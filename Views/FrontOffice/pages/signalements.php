<?php
require_once __DIR__.'/../../../Models/Auth.php';
authStart(); header('Cache-Control: private, no-store');
$pageTitle='Signalements - CivLink'; $currentPage='signalements'; $base='/CivLink/Views/FrontOffice'; $assets=$base.'/assets';
$uid=(int)($_SESSION['user']['id']??0); $categories=[]; $reports=[]; $error=''; $edit=null; $ready=false;
function se($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
try {
 $db=authDb(); $categories=$db->query('SELECT * FROM categorie_signalement ORDER BY nom')->fetchAll(PDO::FETCH_ASSOC); $ready=true;
 if(isset($_GET['photo'])){ $q=$db->prepare('SELECT photo,photoMime FROM signalement WHERE idSignalement=? AND idUtilisateur=?'); $q->execute([(int)$_GET['photo'],$uid]); $p=$q->fetch(PDO::FETCH_ASSOC); if(!$uid||!$p||!$p['photo']){http_response_code(404);exit;} header('Content-Type: '.$p['photoMime']);header('X-Content-Type-Options: nosniff');echo $p['photo'];exit; }
 if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!$uid||!authCheck()) throw new RuntimeException('Session invalide. Reconnectez-vous.');
  $action=authPost('action'); $id=(int)authPost('id');
  if($action==='cancel'){ $q=$db->prepare("UPDATE signalement SET statut='Annulé' WHERE idSignalement=? AND idUtilisateur=? AND statut='En attente'");$q->execute([$id,$uid]);if(!$q->rowCount())throw new RuntimeException('Ce signalement ne peut plus être annulé.'); }
  elseif(in_array($action,['create','edit'],true)){
   $title=trim(authPost('titre'));$desc=trim(authPost('description'));$address=trim(authPost('adresse'));$cat=(int)authPost('categorie');
   if(mb_strlen($title)<5||mb_strlen($title)>120||strlen($desc)<10||strlen($desc)>3000||$address===''||strlen($address)>255||!in_array($cat,array_map('intval',array_column($categories,'idCategorie')),true))throw new RuntimeException('Vérifiez les champs obligatoires (titre : 5 à 120 caractères, description : 10 à 3000 caractères).');
   $lat=authPost('latitude');$lng=authPost('longitude'); if($lat===''&&$lng===''){$lat=$lng=null;}elseif(!is_numeric($lat)||!is_numeric($lng)||abs((float)$lat)>90||abs((float)$lng)>180)throw new RuntimeException('Coordonnées invalides.');
   $photo=$mime=null; $f=$_FILES['photo']??null;
   if($f&&$f['error']!==UPLOAD_ERR_NO_FILE){if($f['error']!==UPLOAD_ERR_OK||$f['size']>2*1024*1024||!is_uploaded_file($f['tmp_name']))throw new RuntimeException('Photo invalide ou supérieure à 2 Mo.');$mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);if(!in_array($mime,['image/jpeg','image/png','image/webp'],true)||!getimagesize($f['tmp_name']))throw new RuntimeException('Utilisez une image JPG, PNG ou WebP.');$photo=file_get_contents($f['tmp_name']);}
   if($action==='create'){$q=$db->prepare('INSERT INTO signalement(idUtilisateur,idCategorie,titre,description,adresse,latitude,longitude,photo,photoMime) VALUES (?,?,?,?,?,?,?,?,?)');$q->execute([$uid,$cat,$title,$desc,$address,$lat,$lng,$photo,$mime]);}
   else{$q=$db->prepare("UPDATE signalement SET idCategorie=?,titre=?,description=?,adresse=?,latitude=?,longitude=?,photo=COALESCE(?,photo),photoMime=COALESCE(?,photoMime) WHERE idSignalement=? AND idUtilisateur=? AND statut='En attente'");$q->execute([$cat,$title,$desc,$address,$lat,$lng,$photo,$mime,$id,$uid]);}
  } else throw new RuntimeException('Action inconnue.');
  authRedirect('signalements.php?ok=1#mes-signalements');
 }
} catch(PDOException $e){$error='Base de données indisponible. '.$e->getMessage();$ready=false;} catch(RuntimeException $e){$error=$e->getMessage();}
if($ready&&$uid){try{$q=$db->prepare('SELECT s.idSignalement,s.idCategorie,s.titre,s.description,s.adresse,s.latitude,s.longitude,s.statut,s.dateSignalement,(s.photo IS NOT NULL) AS hasPhoto,c.nom AS categorie FROM signalement s JOIN categorie_signalement c ON c.idCategorie=s.idCategorie WHERE s.idUtilisateur=? ORDER BY s.idSignalement DESC');$q->execute([$uid]);$reports=$q->fetchAll(PDO::FETCH_ASSOC);foreach($reports as $r)if(isset($_GET['edit'])&&(int)$_GET['edit']==(int)$r['idSignalement']&&$r['statut']==='En attente')$edit=$r;}catch(PDOException $e){$ready=false;$error='Impossible de charger les signalements.';}}
?>
<!DOCTYPE html><html lang="fr"><head><?php require __DIR__.'/../includes/head.php'; ?><link rel="stylesheet" href="<?= $assets ?>/css/signalements.css"><link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"></head><body>
<?php require __DIR__.'/../includes/navbar.php'; ?>
<main class="sig-page"><section class="sig-hero container-xl"><div class="sig-crumb"><a href="<?= $base ?>/index.php">Accueil</a> › Signalements</div><div class="sig-intro"><div><span class="sig-kicker">PARTICIPATION CITOYENNE</span><h1>Votre ville, <span>votre voix.</span></h1><p>Signalez un problème dans votre quartier et suivez son traitement depuis votre espace citoyen.</p><a class="sig-primary" href="#nouveau">Nouveau signalement <i class="fas fa-arrow-right"></i></a> <a class="sig-link" href="#mes-signalements">Mes signalements</a></div><div class="sig-art" aria-hidden="true"><i class="fas fa-map-marker-alt"></i><span><i class="fas fa-bullhorn"></i></span></div></div></section>
<div class="container-xl sig-content">
<?php if($error): ?><p class="sig-error" role="alert"><?= se($error) ?></p><?php endif; ?><?php if(isset($_GET['ok'])&&!$error): ?><p class="sig-note" role="status">Votre demande a été enregistrée.</p><?php endif; ?>
<div class="sig-stats"><?php foreach(['En attente','En cours','Résolu'] as $status): ?><div><span><?= se($status) ?></span><strong><?= count(array_filter($reports,fn($r)=>$r['statut']===$status)) ?></strong></div><?php endforeach; ?></div>
<section id="nouveau" class="sig-panel"><span class="sig-kicker">AGIR POUR SA VILLE</span><h2><?= $edit?'Modifier mon signalement':'Créer un signalement' ?></h2>
<?php if(!$uid): ?><p>Connectez-vous pour envoyer un signalement et consulter votre historique.</p><a class="sig-primary" href="<?= $base ?>/pages/authentification.php">Se connecter</a><?php elseif($ready): ?>
<form method="post" enctype="multipart/form-data" class="sig-form" id="sigForm" novalidate><input type="hidden" name="csrf" value="<?= se(authToken())