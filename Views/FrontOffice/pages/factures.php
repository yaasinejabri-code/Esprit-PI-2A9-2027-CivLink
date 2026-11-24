<?php
require_once __DIR__ . '/../../../Models/Auth.php';
authStart();
header('Cache-Control: private, no-store');
$connected = !empty($_SESSION['user']['id']);
$isAdmin = $connected && ($_SESSION['user']['role'] ?? '') === 'administrateur';
$pageTitle = 'Factures et paiements - CivLink';
$currentPage = 'factures';
$base = '/CivLink/Views/FrontOffice';
$assets = $base . '/assets';
$api = '/CivLink/Controllers/FactureController.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require __DIR__ . '/../includes/head.php'; ?>
    <link rel="stylesheet" href="<?= $assets ?>/css/factures.css">
</head>
<body>
<?php require __DIR__ . '/../includes/navbar.php'; ?>
<main class="factures-page" id="facApp" data-api="<?= htmlspecialchars($api, ENT_QUOTES, 'UTF-8') ?>" data-csrf="<?= $connected ? htmlspecialchars(authToken(), ENT_QUOTES, 'UTF-8') : '' ?>" data-login="<?= $base ?>/pages/authentification.php">
    <section class="fac-hero">
      <div class="container-xl">
        <div class="fac-crumb"><a href="<?= $base ?>/index.php">Accueil</a><i class="fas fa-chevron-right"></i><span>Factures</span></div>
        <div class="row align-items-center gy-4">
          <div class="col-lg-8">
            <span class="fac-eyebrow"><i class="fas fa-shield-alt"></i> Services publics en ligne</span>
            <h1>Vos factures, <span>en toute simplicité.</span></h1>
            <p>Consultez vos factures d’eau et d’électricité, retrouvez l’historique de vos paiements et testez un paiement en quelques clics.</p>
            <div class="fac-hero-actions"><a href="#mes-factures" class="fac-btn fac-btn-orange">Voir mes factures <i class="fas fa-arrow-right"></i></a><a href="#comment-ca-marche" class="fac-hero-link">Comment ça marche ? <i class="fas fa-arrow-down"></i></a></div>
          </div>
          <div class="col-lg-4">
            <div class="fac-hero-art" aria-hidden="true"><span class="fac-art-orbit"></span><div class="fac-art-file"><i class="far fa-file-alt"></i><span></span><span></span><span></span><div class="fac-art-check"><i class="fas fa-check"></i></div></div><div class="fac-art-bolt"><i class="fas fa-bolt"></i></div><div class="fac-art-drop"><i class="fas fa-tint"></i></div></div>
          </div>
        </div>
      </div>
    </section>
    <div class="container-xl"><div class="fac-demo" role="note"><i class="fas fa-info-circle"></i><div><strong>Mode démonstration</strong> — Les factures et montants affichés sont fictifs. Aucun service STEG/SONEDE n’est connecté et aucun paiement réel n’est effectué.</div></div></div>
<?php if (!$connected): ?>
    <section id="mes-factures" class="fac-main container-xl"><div class="fac-panel fac-login"><i class="fas fa-lock"></i><h2>Connexion requise</h2><p>Connectez-vous avec votre compte citoyen pour consulter vos factures et simuler un paiement.</p><a class="fac-btn fac-btn-orange" href="<?= $base ?>/pages/authentification.php">Se connecter <i class="fas fa-arrow-right"></i></a></div></section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php require __DIR__ . '/../includes/scripts.php'; ?>
</body></html>
<?php exit; endif; ?>
    <?php if ($isAdmin): ?><div class="container-xl"><div class="fac-demo" role="note"><i class="fas fa-user-shield"></i><div>Vous êtes administrateur. <a href="<?= $base ?>/pages/factures_admin.php"><strong>Ouvrir la gestion des factures et des modes de paiement →</strong></a></div></div></div><?php endif; ?>
    <section id="mes-factures" class="fac-main container-xl">
      <div class="fac-section-heading"><div><span class="fac-kicker">ESPACE FACTURATION</span><h2>Mes factures</h2><p>Factures créées par l’administration CivLink pour votre compte.</p></div><span class="fac-count" id="facCount">Chargement…</span></div>
      <div class="fac-stats" aria-label="Résumé de facturation">
        <div class="fac-stat"><div class="fac-stat-icon orange"><i class="far fa-file-alt"></i></div><div><small>Factures en attente</small><strong id="statPending">—</strong><span>À régler (simulation)</span></div></div>
        <div class="fac-stat"><div class="fac-stat-icon navy"><i class="fas fa-wallet"></i></div><div><small>Montant à régler</small><strong id="statAmount">—</strong><span>En dinars tunisiens</span></div></div>
        <div class="fac-stat"><div class="fac-stat-icon green"><i class="fas fa-check-circle"></i></div><div><small>Factures réglées</small><strong id="statPaid">—</strong><span>Historique de démonstration</span></div></div>
      </div>
      <div class="fac-panel">
        <div class="fac-toolbar"><div class="fac-tabs" role="group" aria-label="Fournisseur"><button class="is-active" type="button" data-provider="all" aria-pressed="true">Toutes</button><button type="button" data-provider="STEG" aria-pressed="false"><i class="fas fa-bolt"></i> STEG</button><button type="button" data-provider="SONEDE" aria-pressed="false"><i class="fas fa-tint"></i> SONEDE</button></div><label class="fac-search"><i class="fas fa-search"></i><input id="facSearch" type="search" placeholder="Rechercher une référence…" aria-label="Rechercher une facture"></label></div>
        <div id="facList" class="fac-invoices" aria-live="polite"></div>
        <div id="facEmpty" class="fac-empty" hidden><i class="far fa-folder-open"></i><h3>Aucune facture trouvée</h3><p>Essayez une autre référence ou un autre fournisseur.</p></div>
      </div>
      <div class="fac-bottom-grid"><section class="fac-panel fac-history"><div class="fac-panel-heading"><div><span class="fac-kicker">VOS OPÉRATIONS</span><h2>Historique des paiements</h2></div><i class="fas fa-history"></i></div><div id="facHistory" aria-live="polite"></div></section><section class="fac-help" id="comment-ca-marche"><span class="fac-kicker">GUIDE RAPIDE</span><h2>Comment ça marche ?</h2><div class="fac-step"><span>01</span><div><strong>Consultez les factures</strong><p>Choisissez STEG ou SONEDE et recherchez une référence.</p></div></div><div class="fac-step"><span>02</span><div><strong>Vérifiez le montant</strong><p>Consultez les détails, la période et l’échéance.</p></div></div><div class="fac-step"><span>03</span><div><strong>Simulez le paiement</strong><p>Confirmez l’opération pour voir le reçu de démonstration.</p></div></div></section></div>
    </section>
<div class="fac-modal" id="facModal" hidden><div class="fac-modal-scrim" data-close="true"></div><div class="fac-modal-card" role="dialog" aria-modal="true" aria-labelledby="facModalTitle"><button class="fac-close" type="button" id="facClose" aria-label="Fermer"><i class="fas fa-times"></i></button><span class="fac-kicker">PAIEMENT FICTIF</span><h2 id="facModalTitle">Confirmer la simulation</h2><div id="facModalDetail"></div><div id="facModeChoice" hidden></div><p class="fac-error" id="facError" role="alert" hidden></p><p class="fac-modal-notice"><i class="fas fa-lock"></i> Simulation : les informations saisies ne sont ni enregistrées ni transmises.</p><div class="fac-modal-actions"><a class="fac-btn fac-btn-orange" id="facDownload" href="#" hidden download><i class="fas fa-download"></i> Télécharger le reçu</a><button type="button" class="fac-btn fac-btn-outline" id="facCancelPay" hidden>Annuler le paiement</button><button type="button" class="fac-btn fac-btn-outline" id="facCancel">Annuler</button><button type="button" class="fac-btn fac-btn-orange" id="facConfirm">Confirmer la simulation <i class="fas fa-check"></i></button></div></div></div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php require __DIR__ . '/../includes/scripts.php'; ?>
<script src="<?= $assets ?>/js/factures.js?v=4" defer></script>
</body></html>
