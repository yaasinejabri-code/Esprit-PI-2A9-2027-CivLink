<?php
if (!defined('CIVLINK_FRONT_RENDER')) {
    http_response_code(403);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once __DIR__ . '/../includes/head.php'; ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/administrations.css">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <section class="adm-header">
        <div class="container-xl">
            <nav aria-label="Fil d’Ariane" class="adm-crumbs">
                <a href="<?= $base ?>/index.php">Accueil</a>
                <i class="fas fa-chevron-right"></i>
                <span>Administrations</span>
            </nav>
            <h1 class="adm-title">Administrations près de chez vous</h1>
            <p class="adm-lead">Indiquez votre position pour trouver les administrations proches, puis consultez leurs démarches.</p>

            <div class="adm-locate">
                <form class="civlink-search-bar adm-search" id="locForm" role="search">
                    <i class="fas fa-map-marker-alt"></i>
                    <input type="text" id="locQuery" placeholder="Votre adresse, quartier ou ville (ex. : Ariana)"
                           aria-label="Votre position" autocomplete="off">
                    <div id="locSuggestions" class="loc-suggestions"></div>
                </form>
                <button type="button" class="adm-go" id="locGo">Rechercher</button>
                <button type="button" class="adm-geo" id="locGeo"><i class="fas fa-crosshairs"></i>Ma position</button>
                <label class="adm-radius">Rayon
                    <select id="locRadius" aria-label="Rayon de recherche">
                        <option value="2">2 km</option>
                        <option value="5" selected>5 km</option>
                        <option value="10">10 km</option>
                        <option value="25">25 km</option>
                        <option value="0">Tout afficher</option>
                    </select>
                </label>
            </div>
            <p class="adm-status" id="locStatus" aria-live="polite">Astuce : utilisez « Ma position » ou saisissez une adresse pour trier par distance.</p>

            <div class="adm-filters" role="group" aria-label="Filtrer par catégorie">
                <?php foreach ($categories as $key => $label): ?>
                    <button type="button" class="adm-chip<?= $key === 'all' ? ' is-active' : '' ?>"
                            data-cat="<?= h($key) ?>" aria-pressed="<?= $key === 'all' ? 'true' : 'false' ?>">
                        <?= h($label) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="adm-list">
        <div class="container-xl">
            <div class="adm-map-layout">
                <aside class="adm-results">
                    <form class="civlink-search-bar adm-search" role="search" onsubmit="return false">
                        <i class="fas fa-search"></i>
                        <input type="text" id="admQuery" placeholder="Filtrer par nom ou service…"
                               aria-label="Filtrer les administrations" autocomplete="off">
                    </form>
                    <p class="adm-count" id="admCount" aria-live="polite"></p>
                    <ul class="adm-results-list" id="admList"></ul>
                </aside>
                <div class="adm-map" id="admMap" role="application" aria-label="Carte des administrations"></div>
            </div>
        </div>
    </section>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    <?php require_once __DIR__ . '/../includes/scripts.php'; ?>

    <script id="admData" type="application/json"><?= json_encode($admData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="<?= $assets ?>/js/administrations.js"></script>
</body>
</html>
