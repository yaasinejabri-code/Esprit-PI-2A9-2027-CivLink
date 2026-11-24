<?php

require_once __DIR__ . '/../../../Models/CivData.php';

$pageTitle   = "Démarches - CivLink";
$currentPage = "demarches";

$base   = "/CivLink/Views/FrontOffice";
$assets = $base . "/assets";

$categories = CivData::categories();
$types      = CivData::types();

// Démarches ajoutées depuis le Back Office (les exemples existants restent visibles).
try {
    require_once __DIR__ . '/../../../Models/Auth.php';
    $db = authDb();
    $q = $db->query("SELECT d.idDemarche,d.titre,d.description,d.delai,d.cout,d.etapes,
                           a.idAdministration,a.typeAdministration,a.nom AS administrationNom
                    FROM demarche d
                    JOIN administration a ON a.idAdministration=d.idAdministration
                    WHERE d.actif=1 AND a.actif=1
                    ORDER BY d.idDemarche");
    $newDems = $q->fetchAll(PDO::FETCH_ASSOC);
    $piecesStmt = $db->prepare("SELECT dr.nom FROM demarche_document dd
        JOIN document_requis dr ON dr.idDocument=dd.idDocument
        WHERE dd.idDemarche=? AND dr.actif=1 ORDER BY dr.nom");
    foreach ($newDems as $row) {
        $categoryKey = (string)$row['typeAdministration'];
        if (!isset($types[$categoryKey])) continue;
        // Chaque administration possède sa propre fiche : pas de mélange entre agences.
        $key = 'adm-' . (int)$row['idAdministration'];
        if (!isset($types[$key])) {
            $types[$key] = $types[$categoryKey];
            $types[$key]['nom'] = $row['administrationNom'];
            $types[$key]['docs'] = [];
        }
        $piecesStmt->execute([(int)$row['idDemarche']]);
        $pieces = $piecesStmt->fetchAll(PDO::FETCH_COLUMN);
        $etapes = [];
        foreach (preg_split('/\r\n|\r|\n/', (string)$row['etapes']) as $line) {
            if (trim($line)==='') continue;
            $parts=explode('|',$line,2);
            $etapes[]=[trim($parts[0]),trim($parts[1]??'')];
        }
        $types[$key]['docs']['dem-' . (int)$row['idDemarche']] = [
            'titre' => $row['titre'], 'icone' => 'fa-file-alt',
            'desc' => $row['description'], 'delai' => $row['delai'],
            'cout' => $row['cout'], 'pieces' => $pieces,
            'etapes' => $etapes
        ];
    }
} catch (PDOException $e) {
    // Les exemples statiques restent accessibles avant importation du SQL.
}


// ----- Sélection depuis l'URL : ?admin=steg&site=s11&doc=abonnement-elec -----
$adminKey = $_GET['admin'] ?? '';
$admin    = isset($types[$adminKey]) ? $types[$adminKey] : null;
if (!$admin) { $adminKey = ''; }

$docKey = $_GET['doc'] ?? '';
if (!$admin || !isset($admin['docs'][$docKey])) { $docKey = ''; }

$site = null;
foreach (CivData::sites() as $s) {
    if ($s['id'] === ($_GET['site'] ?? '') && $s['type'] === $adminKey) { $site = $s; break; }
}
$siteId = $site ? $site['id'] : null;

// Index de recherche (toutes administrations confondues)
$index = [];
foreach ($types as $tk => $t) {
    foreach ($t['docs'] as $dk => $d) {
        $index[] = ['admin' => $tk, 'adminNom' => $t['nom'], 'doc' => $dk, 'titre' => $d['titre'],
                    'text' => mb_strtolower($d['titre'] . ' ' . $d['desc'] . ' ' . $t['nom'], 'UTF-8')];
    }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php require_once __DIR__ . '/../includes/head.php'; ?>
</head>

<body>

    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <!-- ===== EN-TÊTE ===== -->
    <section class="adm-header">
        <div class="container-xl">
            <nav aria-label="Fil d’Ariane" class="adm-crumbs">
                <a href="<?= $base ?>/index.php">Accueil</a>
                <i class="fas fa-chevron-right"></i>
                <?php if ($admin): ?>
                    <a href="<?= $base ?>/pages/demarches.php">Démarches</a>
                    <i class="fas fa-chevron-right"></i>
                    <span><?= h($admin['nom']) ?></span>
                <?php else: ?>
                    <span>Démarches</span>
                <?php endif; ?>
            </nav>
            <h1 class="adm-title">Démarches administratives</h1>
            <p class="adm-lead">Choisissez une administration, sélectionnez le document dont vous avez besoin et suivez les étapes.</p>

            <form class="civlink-search-bar adm-search" role="search" onsubmit="return false">
                <i class="fas fa-search"></i>
                <input type="text" id="demQuery" placeholder="Rechercher un document ou une démarche…"
                       aria-label="Rechercher une démarche" autocomplete="off">
            </form>
            <ul class="dem-results" id="demResults" hidden></ul>

            <div class="adm-filters" role="group" aria-label="Filtrer les administrations par catégorie">
                <?php foreach ($categories as $key => $label): ?>
                    <button type="button" class="adm-chip<?= $key === 'all' ? ' is-active' : '' ?>"
                            data-cat="<?= h($key) ?>" aria-pressed="<?= $key === 'all' ? 'true' : 'false' ?>">
                        <?= h($label) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

   
    <section class="adm-list dem-section">
        <div class="container-xl">
            <h2 class="dem-step-title"><span class="dem-num">1</span>Choisissez une administration</h2>

            <div class="row g-3" id="demAdmins">
                <?php foreach ($types as $tk => $t): ?>
                    <div class="col-6 col-md-4 col-xl-3 dem-admin-item" data-cat="<?= h($t['cat']) ?>">
                        <a class="dem-admin<?= $tk === $adminKey ? ' is-active' : '' ?>"
                           href="<?= h(CivData::demarchesUrl($base, $tk)) ?>#documents"
                           <?= $tk === $adminKey ? 'aria-current="true"' : '' ?>>
                            <span class="adm-icon"><i class="fas <?= h($t['icone']) ?>"></i></span>
                            <span class="dem-admin-txt">
                                <strong><?= h($t['nom']) ?></strong>
                                <small><?= count($t['docs']) ?> document<?= count($t['docs']) > 1 ? 's' : '' ?></small>
                            </span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="adm-empty" id="demAdminEmpty" hidden>
                <i class="far fa-folder-open"></i>
                <h3>Aucune administration dans cette catégorie</h3>
            </div>

            <?php if ($admin): ?>
                
                <div id="documents" class="dem-anchor"></div>

                <?php if ($site): ?>
                    <div class="dem-site">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <strong><?= h($site['nom']) ?></strong>
                            <span><?= h($site['adresse']) ?> · <?= h($site['horaires']) ?> · <?= h($site['tel']) ?></span>
                        </div>
                        <a href="<?= $base ?>/pages/administrations.php" class="dem-site-link">Changer d’agence</a>
                    </div>
                <?php else: ?>
                    <div class="dem-site">
                        <i class="fas fa-map-marker-alt"></i>
                        <div><strong>Où aller ?</strong><span>Trouvez l’agence <?= h($admin['nom']) ?> la plus proche de vous.</span></div>
                        <a href="<?= $base ?>/pages/administrations.php" class="dem-site-link">Voir la carte</a>
                    </div>
                <?php endif; ?>

                <div class="dem-layout">
                    <div>
                        <h2 class="dem-step-title"><span class="dem-num">2</span>Choisissez un document</h2>
                        <ul class="dem-docs" id="demDocs">
                            <?php foreach ($admin['docs'] as $dk => $d): ?>
                                <li>
                                    <a href="<?= h(CivData::demarchesUrl($base, $adminKey, $siteId, $dk)) ?>#etapes"
                                       class="dem-doc<?= $dk === $docKey ? ' is-active' : '' ?>" data-doc="<?= h($dk) ?>">
                                        <span class="adm-icon"><i class="fas <?= h($d['icone']) ?>"></i></span>
                                        <span class="dem-doc-txt">
                                            <strong><?= h($d['titre']) ?></strong>
                                            <small><i class="far fa-clock"></i> <?= h($d['delai']) ?></small>
                                        </span>
                                        <i class="fas fa-chevron-right dem-doc-arrow"></i>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div id="etapes">
                        <h2 class="dem-step-title"><span class="dem-num">3</span>Étapes à suivre</h2>

                        <div class="dem-placeholder" id="demPlaceholder"<?= $docKey ? ' hidden' : '' ?>>
                            <i class="far fa-hand-point-left"></i>
                            <p>Sélectionnez un document dans la liste pour voir les pièces à fournir et les étapes.</p>
                        </div>

                        <?php foreach ($admin['docs'] as $dk => $d): ?>
                            <article class="dem-panel" data-doc="<?= h($dk) ?>"<?= $dk === $docKey ? '' : ' hidden' ?>>
                                <header class="dem-panel-head">
                                    <span class="adm-icon"><i class="fas <?= h($d['icone']) ?>"></i></span>
                                    <div>
                                        <h3><?= h($d['titre']) ?></h3>
                                        <p><?= h($d['desc']) ?></p>
                                    </div>
                                </header>

                                <div class="dem-facts">
                                    <div><i class="far fa-clock"></i><span>Délai</span><strong><?= h($d['delai']) ?></strong></div>
                                    <div><i class="fas fa-coins"></i><span>Coût</span><strong><?= h($d['cout']) ?></strong></div>
                                    <div><i class="far fa-building"></i><span>Administration</span><strong><?= h($admin['nom']) ?></strong></div>
                                </div>

                                <h4 class="dem-sub">Pièces à fournir</h4>
                                <ul class="dem-pieces">
                                    <?php foreach ($d['pieces'] as $p): ?>
                                        <li><i class="fas fa-check-circle"></i><?= h($p) ?></li>
                                    <?php endforeach; ?>
                                </ul>

                                <h4 class="dem-sub">Les étapes</h4>
                                <ol class="dem-steps">
                                    <?php foreach ($d['etapes'] as $e): ?>
                                        <li>
                                            <strong><?= h($e[0]) ?></strong>
                                            <p><?= h($e[1]) ?></p>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>

                                <div class="dem-actions">
                                    <a href="<?= $base ?>/pages/administrations.php" class="btn-civ btn-civ-primary"><i class="fas fa-map-marker-alt"></i> Trouver une agence</a>
                                    <button type="button" class="btn-civ btn-civ-outline" onclick="window.print()"><i class="fas fa-print"></i> Imprimer</button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    <?php require_once __DIR__ . '/../includes/scripts.php'; ?>

    <script>
    (function () {
        var INDEX = <?= json_encode($index, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
        var DEM_URL = <?= json_encode($base . '/pages/demarches.php') ?>;
        var SITE = <?= json_encode($siteId) ?>;

        function esc(s) {
            return String(s).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        // --- Filtre des administrations par catégorie ---
        var chips = document.querySelectorAll('.adm-chip');
        var items = document.querySelectorAll('.dem-admin-item');
        var emptyMsg = document.getElementById('demAdminEmpty');
        chips.forEach(function (c) {
            c.addEventListener('click', function () {
                var cat = c.dataset.cat, shown = 0;
                chips.forEach(function (x) {
                    var on = x === c; x.classList.toggle('is-active', on); x.setAttribute('aria-pressed', on);
                });
                items.forEach(function (el) {
                    var ok = cat === 'all' || el.dataset.cat === cat; el.hidden = !ok; if (ok) shown++;
                });
                emptyMsg.hidden = shown !== 0;
            });
        });

        // --- Recherche globale de documents ---
        var q = document.getElementById('demQuery'), res = document.getElementById('demResults');
        q.addEventListener('input', function () {
            var v = q.value.trim().toLowerCase();
            if (v.length < 2) { res.hidden = true; res.innerHTML = ''; return; }
            var hits = INDEX.filter(function (d) { return d.text.indexOf(v) !== -1; });
            res.innerHTML = hits.length ? hits.map(function (d) {
                return '<li><a href="' + DEM_URL + '?admin=' + encodeURIComponent(d.admin) + '&doc=' + encodeURIComponent(d.doc) + '#etapes">' +
                    '<strong>' + esc(d.titre) + '</strong><small>' + esc(d.adminNom) + '</small></a></li>';
            }).join('') : '<li class="dem-results-empty">Aucun document trouvé.</li>';
            res.hidden = false;
        });

        // --- Sélection d'un document sans recharger la page ---
        var links = document.querySelectorAll('.dem-doc');
        var panels = document.querySelectorAll('.dem-panel');
        var placeholder = document.getElementById('demPlaceholder');
        links.forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                var id = a.dataset.doc;
                links.forEach(function (x) { x.classList.toggle('is-active', x === a); });
                panels.forEach(function (p) { p.hidden = p.dataset.doc !== id; });
                if (placeholder) placeholder.hidden = true;
                if (history.replaceState) history.replaceState(null, '', a.getAttribute('href'));
                if (window.matchMedia('(max-width: 991px)').matches) {
                    document.getElementById('etapes').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    })();
    </script>
</body>

</html>