<?php

$pageTitle = "Home - CivLink";
$currentPage = "home";

$base   = "/CivLink/Views/FrontOffice";
$assets = $base . "/assets";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once __DIR__ . '/includes/head.php'; ?>
</head>

<body>

    <!-- Navbar -->
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>


    <!-- ===== HERO ===== -->
    <section class="civlink-hero">
        <span class="hero-blob hero-blob-navy"></span>
        <span class="hero-blob hero-blob-orange"></span>

        <div class="container-xl">
            <div class="row align-items-center g-4">

                <!-- Texte -->
                <div class="col-lg-6 position-relative wow fadeInLeft" data-wow-delay="0.1s" style="z-index:2;">
                    <h1 class="hero-title">
                        L’innovation au service<br>
                        <span>des citoyens.</span>
                    </h1>
                    <p class="hero-text">
                        Effectuez vos démarches, gérez vos factures<br class="d-none d-md-inline">
                        et signalez les problèmes de votre commune,<br class="d-none d-md-inline">
                        24h/24 et 7j/7.
                    </p>
                    <div class="hero-actions">
                        <a href="#services" class="btn-civ btn-civ-primary">
                            Découvrir les services <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="<?= $base ?>/pages/authentification.php?mode=inscription" class="btn-civ btn-civ-outline">
                            <i class="fas fa-user-plus"></i> Créer un compte
                        </a>
                    </div>
                </div>

                <!-- Image -->
                <div class="col-lg-6 wow fadeInRight" data-wow-delay="0.3s">
                    <div class="hero-visual">
                        <div class="hero-visual-frame">
                            <i class="fas fa-landmark hero-visual-placeholder"></i>
                            <img src="<?= $assets ?>/img/hero.png" alt="Hôtel de ville — Commune"
                                 onerror="this.style.display='none'">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ===== BARRE DE RECHERCHE ===== -->
    <div class="container-xl">
        <form class="civlink-search-bar wow fadeInUp" data-wow-delay="0.2s" action="#" method="get" role="search">
            <i class="fas fa-search"></i>
            <input type="text" name="q" placeholder="Rechercher une administration, un service, une démarche…"
                   aria-label="Rechercher">
            <button type="submit">Rechercher</button>
        </form>
    </div>


    <!-- ===== SERVICES RAPIDES ===== -->
    <section id="services" class="civlink-services">
        <div class="container-xl">
            <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-lg-5">

                <div class="col wow fadeInUp" data-wow-delay="0.1s">
                    <a href="<?= $base ?>/pages/administrations.php" class="service-card">
                        <i class="fas fa-map-marker-alt service-icon"></i>
                        <span class="service-label">Informations<br>des administrations</span>
                        <i class="fas fa-arrow-right service-arrow"></i>
                    </a>
                </div>

                <div class="col wow fadeInUp" data-wow-delay="0.2s">
                    <a href="<?= $base ?>/pages/demarches.php" class="service-card">
                        <i class="far fa-file-alt service-icon"></i>
                        <span class="service-label">Démarches<br>administratives</span>
                        <i class="fas fa-arrow-right service-arrow"></i>
                    </a>
                </div>

                <div class="col wow fadeInUp" data-wow-delay="0.3s">
                    <a href="#" class="service-card">
                        <i class="fas fa-bullhorn service-icon"></i>
                        <span class="service-label">Signalements<br>citoyens</span>
                        <i class="fas fa-arrow-right service-arrow"></i>
                    </a>
                </div>

                <div class="col wow fadeInUp" data-wow-delay="0.4s">
                    <a href="<?= $base ?>/pages/factures.php" class="service-card">
                        <i class="far fa-credit-card service-icon"></i>
                        <span class="service-label">Paiement<br>des factures<br><em>(simulé)</em></span>
                        <i class="fas fa-arrow-right service-arrow"></i>
                    </a>
                </div>

                <div class="col wow fadeInUp" data-wow-delay="0.5s">
                    <a href="#" class="service-card">
                        <i class="far fa-comment-dots service-icon"></i>
                        <span class="service-label">Assistant<br>FAQ</span>
                        <i class="fas fa-arrow-right service-arrow"></i>
                    </a>
                </div>

            </div>
        </div>
    </section>


    <!-- ===== POURQUOI CHOISIR CIVLINK + ASSISTANT FAQ ===== -->
    <section class="civlink-why">
        <div class="city-skyline"><svg viewBox="0 0 700 120" preserveAspectRatio="xMinYMax meet" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><g fill="#dde3ec"><rect x="0" y="62" width="40" height="58"/><rect x="17" y="12" width="3" height="50"/><path d="M115 120 V70 a24 24 0 0 1 48 0 V120 Z"/><rect x="10" y="50" width="32" height="70"/><rect x="46" y="30" width="44" height="90"/><rect x="94" y="80" width="26" height="40"/><rect x="124" y="60" width="44" height="60"/><rect x="176" y="65" width="26" height="55"/><rect x="214" y="50" width="38" height="70"/><rect x="256" y="50" width="26" height="70"/><rect x="286" y="72" width="26" height="48"/><rect x="320" y="65" width="38" height="55"/><rect x="362" y="50" width="38" height="70"/><rect x="412" y="80" width="38" height="40"/><rect x="462" y="72" width="38" height="48"/><rect x="508" y="65" width="32" height="55"/><rect x="544" y="50" width="44" height="70"/><rect x="592" y="80" width="38" height="40"/><rect x="638" y="60" width="38" height="60"/></g></svg></div>
        <div class="container-xl">
            <div class="row g-4 align-items-start">

                <!-- Pourquoi choisir -->
                <div class="col-lg-7">
                    <h2 class="why-title wow fadeInUp" data-wow-delay="0.1s">Pourquoi choisir CIVLINK ?</h2>
                    <div class="row g-3 why-list">
                        <div class="col-md-4 wow fadeInUp" data-wow-delay="0.2s">
                            <div class="why-item">
                                <i class="fas fa-bolt"></i>
                                <div>
                                    <h5>Rapide</h5>
                                    <p>Des démarches en quelques clics.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 wow fadeInUp" data-wow-delay="0.4s">
                            <div class="why-item">
                                <i class="fas fa-layer-group"></i>
                                <div>
                                    <h5>Centralisé</h5>
                                    <p>Plusieurs administrations réunies sur une seule plateforme.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 wow fadeInUp" data-wow-delay="0.6s">
                            <div class="why-item">
                                <i class="fas fa-laptop"></i>
                                <div>
                                    <h5>Accessible</h5>
                                    <p>Disponible 24h/24 et 7j/7.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <!-- Actualités -->
                <div class="col-lg-5">
                    <div class="faq-card wow fadeInRight" data-wow-delay="0.3s">

                        <div class="faq-head">
                            <i class="fas fa-newspaper"></i>
                            <h5>Actualités</h5>
                        </div>

                        <div class="civ-news-list">
                            <div class="civ-news-item">
                                <span class="civ-news-category">Services publics</span>
                                <h5>Informations et nouveautés administratives</h5>
                                <p>Découvrez les dernières annonces des services publics.</p>
                            </div>

                            <div class="civ-news-item">
                                <span class="civ-news-category">CIVLINK</span>
                                <h5>Vos démarches en un seul endroit</h5>
                                <p>Retrouvez les informations utiles pour vos démarches.</p>
                            </div>
                        </div>

                        <a href="<?= $base ?>/pages/actualites.php" class="civ-news-more">
                            Voir toutes les actualités
                            <i class="fas fa-arrow-right"></i>
                        </a>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- JavaScript files -->
    <?php require_once __DIR__ . '/includes/scripts.php'; ?>

</body>

</html>