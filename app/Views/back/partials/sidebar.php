<?php
$currentPage = $currentPage ?? 'dashboard';
if (!function_exists('sideActive')) {
    function sideActive(string $page, string $currentPage): string {
        return $page === $currentPage ? 'active' : '';
    }
}
?>
<aside class="admin-sidebar">
    <div class="brand">CIV<span>LINK</span></div>
    <div class="sub">Espace d'administration</div>
    <nav>
        <a class="<?= sideActive('dashboard', $currentPage) ?>" href="admin.php?page=dashboard">Tableau de bord</a>
        <a class="<?= sideActive('utilisateurs', $currentPage) ?>" href="admin.php?page=utilisateurs">Utilisateurs &amp; rôles</a>
        <a class="<?= sideActive('administrations', $currentPage) ?>" href="admin.php?page=administrations">Administrations &amp; services</a>
        <a class="<?= sideActive('demarches', $currentPage) ?>" href="admin.php?page=demarches">Démarches &amp; documents</a>
        <a class="<?= sideActive('signalements', $currentPage) ?>" href="admin.php?page=signalements">Signalements &amp; catégories</a>
        <a class="<?= sideActive('factures', $currentPage) ?>" href="admin.php?page=factures">Factures &amp; paiements</a>
        <a class="<?= sideActive('faq', $currentPage) ?>" href="admin.php?page=faq">Assistant FAQ</a>
    </nav>
</aside>