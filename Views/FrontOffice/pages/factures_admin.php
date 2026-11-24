<?php
/** Administration : factures (CRUD, annulation de paiement) et modes de paiement (CRUD). Réservé au rôle « administrateur ». */
require_once __DIR__ . '/../../../Models/Auth.php';
authStart();
header('Cache-Control: private, no-store');

// 1. Non connecté -> page de connexion
if (empty($_SESSION['user']['id'])) { header('Location: /CivLink/Views/FrontOffice/pages/authentification.php'); exit; }
// 2. Rôle relu en base à chaque requête (la session seule ne suffit pas)
$st = authDb()->prepare('SELECT r.nomRole, u.actif FROM utilisateur u JOIN role r ON r.idRole = u.idRole WHERE u.idUtilisateur = ?');
$st->execute([(int) $_SESSION['user']['id']]);
$row = $st->fetch(PDO::FETCH_ASSOC);
if (!$row || !$row['actif'] || $row['nomRole'] !== 'administrateur') {
    http_response_code(403);
    exit('Accès refusé : cet espace est réservé aux administrateurs.');
}

$pageTitle = 'Administration des factures - CivLink';
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
    <link rel="stylesheet" href="<?= $assets ?>/css/factures_admin.css?v=1">
</head>
<body>
<?php require __DIR__ . '/../includes/navbar.php'; ?>
<main class="factures-page fa-admin" id="faApp" data-api="<?= htmlspecialchars($api, ENT_QUOTES, 'UTF-8') ?>" data-csrf="<?= htmlspecialchars(authToken(), ENT_QUOTES, 'UTF-8') ?>">
  <section class="fac-hero fa-hero">
    <div class="container-xl">
      <div class="fac-crumb"><a href="<?= $base ?>/index.php">Accueil</a><i class="fas fa-chevron-right"></i><a href="<?= $base ?>/pages/factures.php">Factures</a><i class="fas fa-chevron-right"></i><span>Administration</span></div>
      <span class="fac-eyebrow"><i class="fas fa-user-shield"></i> Espace administrateur</span>
      <h1>Gestion des <span>factures</span></h1>
      <p>Créez les factures des citoyens, suivez leur règlement, annulez un paiement simulé et gérez les modes de paiement proposés.</p>
    </div>
  </section>

  <div class="container-xl fa-body">
    <div class="fa-toast" id="faToast" role="status" aria-live="polite" hidden></div>

    <div class="fa-stats" aria-label="Résumé">
      <div class="fac-stat"><div class="fac-stat-icon orange"><i class="far fa-file-alt"></i></div><div><small>Factures impayées</small><strong id="sUnpaid">—</strong><span id="sUnpaidAmt"></span></div></div>
      <div class="fac-stat"><div class="fac-stat-icon green"><i class="fas fa-check-circle"></i></div><div><small>Factures réglées</small><strong id="sPaid">—</strong><span id="sPaidAmt"></span></div></div>
      <div class="fac-stat"><div class="fac-stat-icon navy"><i class="fas fa-credit-card"></i></div><div><small>Modes de paiement</small><strong id="sModes">—</strong><span>disponibles aux citoyens</span></div></div>
    </div>

    <div class="fa-tabs" role="tablist">
      <button type="button" role="tab" class="is-active" id="tabF" aria-selected="true" aria-controls="panelF" data-tab="F"><i class="far fa-file-alt"></i> Factures</button>
      <button type="button" role="tab" id="tabM" aria-selected="false" aria-controls="panelM" data-tab="M"><i class="fas fa-credit-card"></i> Modes de paiement</button>
    </div>

    <!-- ===== FACTURES ===== -->
    <section class="fac-panel" id="panelF" role="tabpanel" aria-labelledby="tabF">
      <div class="fac-toolbar">
        <div class="fac-tabs" role="group" aria-label="Fournisseur" id="fProv">
          <button class="is-active" type="button" data-provider="all" aria-pressed="true">Toutes</button>
          <button type="button" data-provider="STEG" aria-pressed="false"><i class="fas fa-bolt"></i> STEG</button>
          <button type="button" data-provider="SONEDE" aria-pressed="false"><i class="fas fa-tint"></i> SONEDE</button>
        </div>
        <select id="fStatut" class="fa-select" aria-label="Filtrer par statut"><option value="">Tous les statuts</option><option value="impayee">Impayées</option><option value="payee">Réglées</option></select>
        <label class="fac-search"><i class="fas fa-search"></i><input id="fSearch" type="search" placeholder="Référence ou citoyen…" aria-label="Rechercher une facture"></label>
        <button type="button" class="fac-btn fac-btn-orange fa-add" id="fAdd"><i class="fas fa-plus"></i> Ajouter une facture</button>
      </div>
      <div class="fa-table-wrap">
        <table class="fa-table" id="fTable">
          <thead><tr><th>Référence</th><th>Citoyen</th><th>Fournisseur</th><th>Émission</th><th>Échéance</th><th class="num">Montant</th><th>Statut</th><th class="act">Actions</th></tr></thead>
          <tbody id="fBody"></tbody>
        </table>
      </div>
      <div id="fEmpty" class="fac-empty" hidden><i class="far fa-folder-open"></i><h3>Aucune facture trouvée</h3><p>Modifiez les filtres ou ajoutez une facture.</p></div>
    </section>

    <!-- ===== MODES DE PAIEMENT ===== -->
    <section class="fac-panel" id="panelM" role="tabpanel" aria-labelledby="tabM" hidden>
      <div class="fac-panel-heading"><div><span class="fac-kicker">MODES DE PAIEMENT</span><h2>Modes proposés aux citoyens</h2></div><button type="button" class="fac-btn fac-btn-orange fa-add" id="mAdd"><i class="fas fa-plus"></i> Ajouter un mode</button></div>
      <div class="fa-table-wrap">
        <table class="fa-table">
          <thead><tr><th>Libellé</th><th>Description</th><th class="num">Factures</th><th class="act">Actions</th></tr></thead>
          <tbody id="mBody"></tbody>
        </table>
      </div>
      <div id="mEmpty" class="fac-empty" hidden><i class="fas fa-credit-card"></i><h3>Aucun mode de paiement</h3><p>Les citoyens ne pourront pas payer tant qu’aucun mode n’existe.</p></div>
    </section>
  </div>

<!-- Formulaire facture (ajouter / modifier) -->
<div class="fac-modal" id="fModal" hidden><div class="fac-modal-scrim" data-close="true"></div><div class="fac-modal-card" role="dialog" aria-modal="true" aria-labelledby="fModalTitle">
  <button class="fac-close" type="button" data-close="true" aria-label="Fermer"><i class="fas fa-times"></i></button>
  <span class="fac-kicker">FACTURE</span><h2 id="fModalTitle">Ajouter une facture</h2>
  <form id="fForm" novalidate autocomplete="off">
    <input type="hidden" name="id" value="">
    <div class="fac-field"><label for="ffCit">Citoyen *</label><select id="ffCit" name="idUtilisateur" required><option value="">— Sélectionner le citoyen —</option></select><small class="fa-err" data-for="idUtilisateur"></small></div>
    <div class="fac-row">
      <div class="fac-field"><label for="ffRef">Référence *</label><input id="ffRef" name="reference" maxlength="60" placeholder="STEG-2026-00412" required><small class="fa-err" data-for="reference"></small></div>
      <div class="fac-field"><label for="ffFour">Fournisseur *</label><select id="ffFour" name="fournisseur" required><option value="STEG">STEG</option><option value="SONEDE">SONEDE</option></select><small class="fa-err" data-for="fournisseur"></small></div>
    </div>
    <div class="fac-field"><label for="ffMont">Montant (DT) *</label><input id="ffMont" name="montant" inputmode="decimal" placeholder="128.450" required><small class="fa-err" data-for="montant"></small></div>
    <div class="fac-row">
      <div class="fac-field"><label for="ffEm">Date d’émission *</label><input id="ffEm" type="date" name="dateEmission" required><small class="fa-err" data-for="dateEmission"></small></div>
      <div class="fac-field"><label for="ffEc">Échéance *</label><input id="ffEc" type="date" name="dateEcheance" required><small class="fa-err" data-for="dateEcheance"></small></div>
    </div>
    <p class="fac-error" id="fFormError" role="alert" hidden></p>
    <div class="fac-modal-actions"><button type="button" class="fac-btn fac-btn-outline" data-close="true">Annuler</button><button type="submit" class="fac-btn fac-btn-orange" id="fSubmit">Enregistrer</button></div>
  </form>
</div></div>

<!-- Formulaire mode de paiement -->
<div class="fac-modal" id="mModal" hidden><div class="fac-modal-scrim" data-close="true"></div><div class="fac-modal-card" role="dialog" aria-modal="true" aria-labelledby="mModalTitle">
  <button class="fac-close" type="button" data-close="true" aria-label="Fermer"><i class="fas fa-times"></i></button>
  <span class="fac-kicker">MODE DE PAIEMENT</span><h2 id="mModalTitle">Ajouter un mode</h2>
  <form id="mForm" novalidate autocomplete="off">
    <input type="hidden" name="id" value="">
    <div class="fac-field"><label for="mfLib">Libellé *</label><input id="mfLib" name="libelle" maxlength="80" required><small class="fa-err" data-for="libelle"></small></div>
    <div class="fac-field"><label for="mfDesc">Description</label><input id="mfDesc" name="description" maxlength="255"><small class="fa-err" data-for="description"></small></div>
    <p class="fac-hint">Astuce : un libellé contenant « carte », « virement », « espèces » ou « mobile » déclenche l’écran de saisie correspondant côté citoyen.</p>
    <p class="fac-error" id="mFormError" role="alert" hidden></p>
    <div class="fac-modal-actions"><button type="button" class="fac-btn fac-btn-outline" data-close="true">Annuler</button><button type="submit" class="fac-btn fac-btn-orange" id="mSubmit">Enregistrer</button></div>
  </form>
</div></div>

</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php require __DIR__ . '/../includes/scripts.php'; ?>
<script src="<?= $assets ?>/js/factures_admin.js?v=1" defer></script>
</body></html>
