<?php
$currentPage = $currentPage ?? '';
if (!function_exists('navActive')) {
    function navActive(string $page, string $currentPage): string {
        return $page === $currentPage ? 'active' : '';
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-civ">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="index.php">CIV<span style="color:#F07832">LINK</span></a>
        <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link <?= navActive('home', $currentPage) ?>" href="index.php">Accueil</a></li>
                <li class="nav-item"><a class="nav-link <?= navActive('administrations', $currentPage) ?>" href="index.php?page=administrations">Administrations</a></li>
                <li class="nav-item"><a class="nav-link <?= navActive('demarches', $currentPage) ?>" href="index.php?page=demarches">Démarches</a></li>
                <li class="nav-item"><a class="nav-link <?= navActive('signalements', $currentPage) ?>" href="index.php?page=signalements">Signalements</a></li>
                <li class="nav-item"><a class="nav-link <?= navActive('factures', $currentPage) ?>" href="index.php?page=factures">Factures</a></li>
                <li class="nav-item"><a class="nav-link <?= navActive('faq', $currentPage) ?>" href="index.php?page=faq">Assistant FAQ</a></li>
            </ul>
        </div>
    </div>
</nav>