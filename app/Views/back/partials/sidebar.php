<?php $currentPage = $currentPage ?? 'dashboard'; $base = BASE_URL . '/admin.php?page='; ?>
<aside class="sidebar" style="background:var(--civ-midnight)">
    <h2 class="p-4" style="color:var(--civ-orange)">CivLink</h2>
    <nav>
        <a href="<?= $base ?>dashboard" class="<?= isActive('dashboard', $currentPage) ?>">Dashboard</a>
        <a href="<?= $base ?>users" class="<?= isActive('users', $currentPage) ?>">Utilisateurs</a>
        <a href="<?= $base ?>administrations" class="<?= isActive('administrations', $currentPage) ?>">Administrations</a>
        <a href="<?= $base ?>demarches" class="<?= isActive('demarches', $currentPage) ?>">Démarches</a>
        <a href="<?= $base ?>signalements" class="<?= isActive('signalements', $currentPage) ?>">Signalements</a>
        <a href="<?= $base ?>factures" class="<?= isActive('factures', $currentPage) ?>">Factures</a>
        <a href="<?= $base ?>faq" class="<?= isActive('faq', $currentPage) ?>">Assistant FAQ</a>
    </nav>
</aside>
