<?php
require_once __DIR__.'/../../../Models/Auth.php';
authStart();
$register = ($_GET['mode'] ?? '') === 'inscription';
if (isset($_SESSION['user'])) authRedirect(($_SESSION['user']['role'] ?? '') === 'administrateur' ? 'choix-espace.php' : 'compte.php');
$base='/CivLink/Views/FrontOffice'; $assets=$base.'/assets'; $currentPage='auth';
$pageTitle=($register?'Créer un compte':'Connexion').' - CivLink';
$error=''; $nom=''; $prenom=''; $email='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nom=trim(authPost('nom')); $prenom=trim(authPost('prenom')); $email=strtolower(trim(authPost('email'))); $password=authPost('password');
    if (!authCheck()) $error='Session expirée. Réessayez.';
    elseif (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>254 || $password==='') $error='Vérifiez votre adresse e-mail et votre mot de passe.';
    elseif ($register && ($nom==='' || $prenom==='' || strlen($nom)>80 || strlen($prenom)>80 || strlen($password)<8 || strlen($password)>72 || str_contains($password, "\0") || $password!==authPost('confirmation'))) $error='Vérifiez vos noms et vos mots de passe (8 à 72 octets, identiques).';
    elseif (!$register && ($_SESSION['retry_after'] ?? 0)>time()) $error='Trop de tentatives. Réessayez dans une minute.';
    else {
        try {
            $db=authDb();
            if ($register) {
                $role=$db->query("SELECT idRole FROM role WHERE nomRole='citoyen'")->fetchColumn();
                if (!$role) throw new RuntimeException('Rôle absent');
                $q=$db->prepare('INSERT INTO utilisateur(nom,prenom,email,motDePasseHash,idRole) VALUES (?,?,?,?,?)');
                $q->execute([$nom,$prenom,$email,password_hash($password,PASSWORD_DEFAULT),$role]);
                authLogin(['idUtilisateur'=>$db->lastInsertId(),'nom'=>$nom,'prenom'=>$prenom,'email'=>$email,'nomRole'=>'citoyen']);
                authRedirect('compte.php');
            } else {
                $q=$db->prepare('SELECT u.*,r.nomRole FROM utilisateur u JOIN role r ON r.idRole=u.idRole WHERE u.email=? AND u.actif=1');
                $q->execute([$email]); $user=$q->fetch(PDO::FETCH_ASSOC);
                if ($user && password_verify($password,$user['motDePasseHash'])) { authLogin($user); authRedirect(($user['nomRole'] ?? '') === 'administrateur' ? 'choix-espace.php' : 'compte.php'); }
                $_SESSION['failures']=($_SESSION['failures'] ?? 0)+1;
                if ($_SESSION['failures']>=5) { $_SESSION['retry_after']=time()+60; $_SESSION['failures']=0; }
                $error='Identifiants incorrects.';
            }
        } catch (PDOException $e) {
            $error=$e->getCode()==='23000' && $register ? 'Cette adresse e-mail est déjà utilisée.' : 'Base de données indisponible. Vérifiez MySQL et importez database_auth.sql.';
        } catch (RuntimeException $e) { $error='Configuration de la base de données incomplète.'; }
    }
}
?>
<!DOCTYPE html><html lang="fr"><head><?php require __DIR__.'/../includes/head.php'; ?><link rel="stylesheet" href="<?= $assets ?>/css/auth.css"></head><body>
<?php require __DIR__.'/../includes/navbar.php'; ?>
<main class="auth-page"><div class="auth-shell">
<aside class="auth-intro"><div class="auth-emblem"><i class="fas fa-landmark"></i></div><span class="auth-kicker">VOTRE ESPACE CITOYEN</span><h1>Les services publics,<br><span>à portée de main.</span></h1><p>Un compte unique pour accéder à votre espace personnel CIVLINK.</p><div class="auth-benefit"><i class="fas fa-check-circle"></i> Un espace personnel</div><div class="auth-benefit"><i class="fas fa-check-circle"></i> Des démarches simplifiées</div><div class="auth-benefit"><i class="fas fa-check-circle"></i> Une connexion sécurisée par mot de passe</div><a href="<?= $base ?>/index.php" class="auth-back">← Retour à l'accueil</a></aside>
<section class="auth-form"><span class="auth-kicker">BIENVENUE SUR CIVLINK</span><h2><?= $register?'Créer un compte':'Connexion' ?></h2><p><?= $register?'Renseignez vos informations pour rejoindre CIVLINK.':'Connectez-vous pour accéder à votre espace citoyen.' ?></p>
<?php if ($error): ?><div class="auth-error" role="alert"><?= authEscape($error) ?></div><?php endif; ?>
<form method="post" action="<?= $base ?>/pages/authentification.php<?= $register?'?mode=inscription':'' ?>"><input type="hidden" name="csrf" value="<?= authToken() ?>">
<?php if ($register): ?><div class="auth-names"><label>Prénom<input name="prenom" autocomplete="given-name" maxlength="80" required value="<?= authEscape($prenom) ?>" placeholder="Votre prénom"></label><label>Nom<input name="nom" autocomplete="family-name" maxlength="80" required value="<?= authEscape($nom) ?>" placeholder="Votre nom"></label></div><?php endif; ?>
<label>Adresse e-mail<input type="email" name="email" autocomplete="email" maxlength="254" required value="<?= authEscape($email) ?>" placeholder="exemple@email.tn"></label>
<label>Mot de passe<div class="auth-password"><input type="password" name="password" id="authPassword" autocomplete="<?= $register?'new-password':'current-password' ?>" <?= $register?'minlength="8" maxlength="72"':'' ?> required placeholder="Votre mot de passe"><button type="button" data-toggle="authPassword" aria-label="Afficher le mot de passe"><i class="far fa-eye"></i></button></div></label>
<?php if ($register): ?><small>Au moins 8 caractères, maximum 72 octets.</small><label>Confirmer le mot de passe<div class="auth-password"><input type="password" name="confirmation" id="authConfirm" autocomplete="new-password" required placeholder="Confirmez votre mot de passe"><button type="button" data-toggle="authConfirm" aria-label="Afficher le mot de passe"><i class="far fa-eye"></i></button></div></label><?php endif; ?>
<button class="auth-submit" type="submit"><?= $register?'Créer mon compte':'Se connecter' ?> <i class="fas fa-arrow-right"></i></button></form>
<p class="auth-switch"><?= $register?'Vous avez déjà un compte ?':'Pas encore de compte ?' ?> <a href="<?= $base ?>/pages/authentification.php<?= $register?'':'?mode=inscription' ?>"><?= $register?'Se connecter':'Créer un compte' ?></a></p></section></div></main>
<?php require __DIR__.'/../includes/footer.php'; require __DIR__.'/../includes/scripts.php'; ?>
<script>document.querySelectorAll('[data-toggle]').forEach(function(b){b.addEventListener('click',function(){var i=document.getElementById(b.dataset.toggle);var show=i.type==='password';i.type=show?'text':'password';b.setAttribute('aria-label',show?'Masquer le mot de passe':'Afficher le mot de passe');b.querySelector('i').className=show?'far fa-eye-slash':'far fa-eye';});});</script></body></html>
