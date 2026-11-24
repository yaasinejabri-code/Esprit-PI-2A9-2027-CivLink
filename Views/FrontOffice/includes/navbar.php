<?php
// Page courante : définir $currentPage dans la page avant d'inclure ce fichier
// valeurs possibles : home, administrations, demarches, signalements, factures, faq
if (!isset($currentPage)) {
    $currentPage = '';
}
// Valeurs par défaut si la page n'a pas défini $base / $assets
$base   = $base   ?? "/CivLink/Views/FrontOffice";
$assets = $assets ?? ($base . "/assets");

if (!function_exists('navActive')) {
    function navActive($page, $currentPage)
    {
        return $page === $currentPage ? 'active' : '';
    }
}
?>

<script>
// Applique le thème enregistré (ou celui du système) avant l'affichage, pour éviter un flash
(function () {
    var t = null;
    try { t = localStorage.getItem('civlink-theme'); } catch (e) {}
    if (!t) { t = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'; }
    document.documentElement.setAttribute('data-theme', t);
})();
</script>

<header class="civlink-header">
    <nav class="navbar navbar-expand-xl civlink-nav">
        <div class="container-fluid px-3 px-lg-5">

            <!-- Logo + texte : un seul lien vers l'accueil -->
            <a href="<?= $base ?>/index.php" class="civlink-brand" aria-label="CivLink, retour à l’accueil">
                <img src="<?= $assets ?>/img/logo1.png" alt="CIVLINK" class="civlink-logo logo-light">
                <img src="<?= $assets ?>/img/logo2.png" alt="CIVLINK" class="civlink-logo logo-dark">
                <span class="civlink-brand-sep"></span>
                <span class="civlink-brand-text">
                    <span class="civlink-wordmark">CIV<span>LINK</span></span>
                    <small>Services publics en ligne</small>
                </span>
            </a>

            <!-- Bouton mobile -->
            <button class="navbar-toggler civlink-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarCollapse" aria-controls="navbarCollapse"
                    aria-expanded="false" aria-label="Menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menu -->
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav ms-auto align-items-xl-center civlink-menu">
                    <li class="nav-item">
                        <a href="<?= $base ?>/index.php" class="nav-link <?= navActive('home', $currentPage) ?>">
                            <i class="fas fa-home"></i>Accueil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $base ?>/pages/administrations.php" class="nav-link <?= navActive('administrations', $currentPage) ?>">
                            <i class="fas fa-landmark"></i>Administrations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $base ?>/pages/demarches.php" class="nav-link <?= navActive('demarches', $currentPage) ?>">
                            <i class="fas fa-file-alt"></i>Démarches
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $base ?>/pages/signalements.php" class="nav-link <?= navActive('signalements', $currentPage) ?>">
                            <i class="fas fa-bullhorn"></i>Signalements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $base ?>/pages/factures.php" class="nav-link <?= navActive('factures', $currentPage) ?>">
                            <i class="far fa-credit-card"></i>Factures
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $base ?>/pages/rendezvous.php"
                        class="nav-link <?= navActive('rendezvous', $currentPage) ?>">
                            <i class="fas fa-calendar-check"></i>
                            Rendez-vous
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $base ?>/pages/actualites.php"
                        class="nav-link <?= navActive('actualites', $currentPage) ?>">
                            <i class="fas fa-newspaper"></i>
                            Actualités
                        </a>
                    </li>
                    <li class="nav-item civlink-divider d-none d-xl-block"></li>
                    <li class="nav-item">
                        <a href="#" class="nav-link civlink-search" aria-label="Rechercher">
                            <i class="fas fa-search m-0"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="theme-toggle" id="themeToggle" aria-label="Basculer le mode sombre">
                            <i class="fas fa-moon icon-moon"></i>
                            <i class="fas fa-sun icon-sun"></i>
                        </button>
                    </li>
                    <li class="nav-item ms-xl-2 mt-2 mt-xl-0">
                        <a href="<?= $base ?>/pages/authentification.php" class="civlink-login" id="civlinkAuthLink">
                            <i class="far fa-user"></i>Connexion
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('themeToggle');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        try { localStorage.setItem('civlink-theme', next); } catch (e) {}
    });
});
</script>
<script>fetch(<?= json_encode($base.'/pages/session.php') ?>,{credentials:'same-origin',cache:'no-store'}).then(function(r){if(!r.ok)throw Error();return r.json();}).then(function(s){if(s.connected){var a=document.getElementById('civlinkAuthLink');if(a){a.href=<?= json_encode($base.'/pages/compte.php') ?>;a.innerHTML='<i class="far fa-user"></i>Mon compte';}}}).catch(function(){});</script>
