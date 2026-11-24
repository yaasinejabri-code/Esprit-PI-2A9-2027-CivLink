<?php
require_once __DIR__ . '/../../../Models/Auth.php';
authStart();
header('Cache-Control: private, no-store');

if (!isset($_SESSION['user'])) {
    authRedirect('authentification.php');
}
// Un citoyen ne voit jamais ce choix.
if (($_SESSION['user']['role'] ?? '') !== 'administrateur') {
    authRedirect('compte.php');
}

$pageTitle = 'Choisir un espace - CIVLINK';
$currentPage = 'auth';
$base = '/CivLink/Views/FrontOffice';
$assets = $base . '/assets';
$prenom = authEscape((string)($_SESSION['user']['prenom'] ?? ''));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require __DIR__ . '/../includes/head.php'; ?>
    <style>
      .espace-choix { min-height: calc(100vh - 165px); display: flex; align-items: center; justify-content: center; padding: 50px 18px; background: #f5f7fa; }
      .espace-choix-contenu { width: min(760px, 100%); text-align: center; }
      .espace-choix h1 { color: #203047; font-size: clamp(25px, 4vw, 38px); font-weight: 800; margin-bottom: 13px; }
      .espace-choix p { color: #61728a; font-size: 16px; margin-bottom: 32px; }
      .espace-options { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 18px; }
      .espace-option { display: flex; flex-direction: column; align-items: center; gap: 12px; border: 1px solid #dce4ee; border-radius: 18px; padding: 32px 20px; background: #fff; color: #223143; text-decoration: none; box-shadow: 0 8px 24px rgba(24,47,75,.07); transition: transform .2s, border-color .2s; }
      .espace-option:hover, .espace-option:focus-visible { transform: translateY(-3px); border-color: #fa8c1b; color: #223143; }
      .espace-option i { color: #f58220; font-size: 38px; }
      .espace-option strong { font-size: 19px; }
      .espace-option span { font-size: 14px; color: #64748b; }
      [data-theme="dark"] .espace-choix { background: #101a29; }
      [data-theme="dark"] .espace-choix h1 { color: #f2f6fd; }
      [data-theme="dark"] .espace-choix p { color: #aab8c9; }
      [data-theme="dark"] .espace-option { background: #1d2b3d; color: #f2f6fd; border-color: #35465a; }
      [data-theme="dark"] .espace-option:hover { color: #f2f6fd; }
      [data-theme="dark"] .espace-option span { color: #afbdcd; }
      @media(max-width: 600px) { .espace-options { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php require __DIR__ . '/../includes/navbar.php'; ?>
<main class="espace-choix">
  <div class="espace-choix-contenu">
    <h1>Bienvenue, <?= $prenom ?> !</h1>
    <p>Vous êtes connecté en tant qu'administrateur. Choisissez l'espace auquel vous souhaitez accéder.</p>
    <div class="espace-options">
      <a class="espace-option" href="<?= $base ?>/index.php">
        <i class="fas fa-home" aria-hidden="true"></i>
        <strong>Front Office</strong>
        <span>Accéder au site CIVLINK comme citoyen</span>
      </a>
      <a class="espace-option" href="/CivLink/Views/BackOffice/index.php?page=dashboard">
        <i class="fas fa-tools" aria-hidden="true"></i>
        <strong>Back Office</strong>
        <span>Gérer les services et les utilisateurs</span>
      </a>
    </div>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php require __DIR__ . '/../includes/scripts.php'; ?>
</body>
</html>
