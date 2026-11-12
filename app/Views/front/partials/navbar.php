<?php $currentPage = $currentPage ?? ''; $base = BASE_URL . '/index.php?page='; ?>
<nav class="navbar navbar-expand-lg shadow sticky-top p-0">
    <a href="<?= $base ?>home" class="navbar-brand px-4"><h2 class="m-0">CivLink</h2></a>
    <div class="navbar-nav ms-auto p-4">
        <a href="<?= $base ?>home" class="nav-item nav-link <?= isActive('home', $currentPage) ?>">Accueil</a>
        <a href="<?= $base ?>administrations" class="nav-item nav-link <?= isActive('administrations', $currentPage) ?>">Administrations</a>
        <a href="<?= $base ?>demarches" class="nav-item nav-link <?= isActive('demarches', $currentPage) ?>">Démarches</a>
        <a href="<?= $base ?>signalements" class="nav-item nav-link <?= isActive('signalements', $currentPage) ?>">Signalements</a>
        <a href="<?= $base ?>factures" class="nav-item nav-link <?= isActive('factures', $currentPage) ?>">Factures</a>
        <a href="<?= $base ?>faq" class="nav-item nav-link <?= isActive('faq', $currentPage) ?>">Assistant FAQ</a>
    </div>
    <a href="<?= $base ?>login" class="btn btn-civ py-4 px-lg-5">Connexion</a>
</nav>
