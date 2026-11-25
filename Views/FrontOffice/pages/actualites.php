
<?php

/*
 * CIVLINK — Actualités
 * Contenus pédagogiques de démonstration,
 * sans annonces officielles.
 */

// ========================================
// 1. PAGE CONFIGURATION
// ========================================

$pageTitle = 'Actualités - CIVLINK';
$currentPage = 'actualites';

$base = '/CivLink/Views/FrontOffice';
$assets = $base . '/assets';

$articles = [];
$categories = [];
$dbError = '';


// ========================================
// 2. LOAD ARTICLES FROM DATABASE
// ========================================

try {
    require_once __DIR__ . '/../../../Models/Auth.php';

    $db = authDb();

    // Load published articles
    $rows = $db->query(
        "SELECT
            a.idActualite AS id,
            c.nom AS categorie,
            a.titre,
            a.resume,
            a.contenu,
            a.icone AS icon,
            a.lecture,
            DATE_FORMAT(a.datePublication, '%Y-%m-%d') AS date,
            a.lien,
            a.lienTexte
         FROM actualite a
         INNER JOIN categorie_actualite c
             ON c.idCategorie = a.idCategorie
         WHERE a.statut = 'publie'
           AND a.datePublication <= NOW()
         ORDER BY
             a.datePublication DESC,
             a.idActualite DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {

        $row['id'] = (int)$row['id'];

        $row['contenu'] = preg_split(
            '/\R\s*\*\R/u',
            trim($row['contenu'])
        ) ?: [];

        $articles[] = $row;
    }

    // Load categories containing published articles
    $categories = $db->query(
        "SELECT DISTINCT c.nom
         FROM categorie_actualite c
         JOIN actualite a
             ON a.idCategorie = c.idCategorie
         WHERE a.statut = 'publie'
           AND a.datePublication <= NOW()
         ORDER BY c.nom"
    )->fetchAll(PDO::FETCH_COLUMN);

} catch (Throwable $e) {

    $dbError = 'Impossible de charger les actualités. Vérifiez que la base civlink_auth et les tables Actualités ont été importées.';
}


// ========================================
// 3. HELPER FUNCTIONS
// ========================================

// Escape HTML
function actEsc($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


// Format date in French
$prettyDate = function ($s) {

    $months = [
        1  => 'janvier',
        2  => 'février',
        3  => 'mars',
        4  => 'avril',
        5  => 'mai',
        6  => 'juin',
        7  => 'juillet',
        8  => 'août',
        9  => 'septembre',
        10 => 'octobre',
        11 => 'novembre',
        12 => 'décembre'
    ];

    $d = explode('-', $s);

    return (int)$d[2] . ' ' .
        $months[(int)$d[1]] . ' ' .
        $d[0];
};


// ========================================
// 4. SELECT ARTICLE FROM URL
// ========================================

$selectedId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

$selected = null;

foreach ($articles as $a) {

    if ($a['id'] === $selectedId) {
        $selected = $a;
        break;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <?php require __DIR__ . '/../includes/head.php'; ?>

    <link
        rel="stylesheet"
        href="<?= actEsc($assets) ?>/css/actualites.css"
    >

</head>

<body>

<?php require __DIR__ . '/../includes/navbar.php'; ?>


<!-- ======================================== -->
<!-- 5. MAIN CONTENT                          -->
<!-- ======================================== -->

<main class="act-page">

    <!-- ======================================== -->
    <!-- HERO SECTION                             -->
    <!-- ======================================== -->

    <section class="act-hero">

        <div class="container-xl">

            <!-- Breadcrumb -->

            <nav
                class="act-breadcrumb"
                aria-label="Fil d’Ariane"
            >

                <a href="<?= actEsc($base) ?>/index.php">
                    Accueil
                </a>

                <i class="fas fa-chevron-right"></i>

                <?php if ($selected): ?>

                    <a href="actualites.php">
                        Actualités
                    </a>

                    <i class="fas fa-chevron-right"></i>

                    <span>
                        <?= actEsc($selected['titre']) ?>
                    </span>

                <?php else: ?>

                    <span>Actualités</span>

                <?php endif; ?>

            </nav>


            <!-- Page heading -->

            <span class="act-kicker">
                <i class="far fa-newspaper"></i>
                ESPACE ACTUALITÉS
            </span>

            <?php if ($selected): ?>

                <h1>
                    <?= actEsc($selected['titre']) ?>
                </h1>

                <p>
                    <?= actEsc($selected['resume']) ?>
                </p>

            <?php else: ?>

                <h1>
                    Les actualités,
                    <span>au cœur de votre ville.</span>
                </h1>

                <p>
                    Conseils pratiques, informations et
                    nouveautés du projet CIVLINK :
                    restez informé et retrouvez les
                    services utiles.
                </p>

            <?php endif; ?>

        </div>

    </section>


    <!-- ======================================== -->
    <!-- ARTICLES CONTENT                         -->
    <!-- ======================================== -->

    <section class="container-xl act-content">

        <!-- Database error -->

        <?php if ($dbError): ?>

            <div class="act-notice" role="alert">

                <i class="fas fa-exclamation-triangle"></i>

                <?= actEsc($dbError) ?>

            </div>

        <?php endif; ?>


        <!-- ======================================== -->
        <!-- 6. ARTICLE DETAILS                      -->
        <!-- ======================================== -->

        <?php if ($selected): ?>

            <article class="act-detail">

                <!-- Article information -->

                <div class="act-detail-meta">

                    <span class="act-category">
                        <?= actEsc($selected['categorie']) ?>
                    </span>

                    <span>
                        <i class="far fa-calendar"></i>

                        <?= actEsc(
                            $prettyDate($selected['date'])
                        ) ?>
                    </span>

                    <span>
                        <i class="far fa-clock"></i>

                        <?= actEsc($selected['lecture']) ?>
                        de lecture
                    </span>

                </div>


                <!-- Article icon -->

                <div class="act-detail-icon">

                    <i class="<?= actEsc($selected['icon']) ?>"></i>

                </div>


                <!-- Article paragraphs -->

                <?php foreach ($selected['contenu'] as $paragraph): ?>

                    <p>
                        <?= actEsc($paragraph) ?>
                    </p>

                <?php endforeach; ?>


                <!-- Article actions -->

                <div class="act-detail-actions">

                    <a
                        class="act-button"
                        href="<?= actEsc($selected['lien']) ?>"
                    >
                        <?= actEsc($selected['lienTexte']) ?>

                        <i class="fas fa-arrow-right"></i>
                    </a>

                    <a class="act-back" href="actualites.php">

                        <i class="fas fa-arrow-left"></i>

                        Toutes les actualités
                    </a>

                </div>

            </article>


            <!-- ======================================== -->
            <!-- 7. RELATED ARTICLES                     -->
            <!-- ======================================== -->

            <h2 class="act-subheading">
                À découvrir aussi
            </h2>

            <div class="act-grid act-related">

                <?php
                $shown = 0;

                foreach ($articles as $a):

                    if ($a['id'] === $selected['id']) {
                        continue;
                    }

                    if ($shown++ >= 3) {
                        break;
                    }
                ?>

                    <article class="act-card">

                        <div class="act-card-top">

                            <div class="act-card-icon">
                                <i class="<?= actEsc($a['icon']) ?>"></i>
                            </div>

                            <span class="act-category">
                                <?= actEsc($a['categorie']) ?>
                            </span>

                        </div>

                        <h3>
                            <?= actEsc($a['titre']) ?>
                        </h3>

                        <p>
                            <?= actEsc($a['resume']) ?>
                        </p>

                        <a href="?id=<?= (int)$a['id'] ?>">

                            Lire l’article

                            <i class="fas fa-arrow-right"></i>

                        </a>

                    </article>

                <?php endforeach; ?>

            </div>


        <!-- ======================================== -->
        <!-- 8. ALL ARTICLES                          -->
        <!-- ======================================== -->

        <?php else: ?>

            <!-- Section heading -->

            <div class="act-section-title">

                <div>

                    <span class="act-kicker">
                        À LA UNE
                    </span>

                    <h2>
                        Explorer les actualités
                    </h2>

                </div>

                <span id="actCount" class="act-count">
                    <?= count($articles) ?> articles
                </span>

            </div>


            <!-- ======================================== -->
            <!-- 9. SEARCH AND FILTERS                   -->
            <!-- ======================================== -->

            <div class="act-toolbar">

                <!-- Search input -->

                <div class="act-search-wrap">

                    <label class="act-search" for="actSearch">

                        <i class="fas fa-search"></i>

                        <input
                            id="actSearch"
                            type="search"
                            autocomplete="off"
                            placeholder="Rechercher un article..."
                            aria-label="Rechercher un article"
                            aria-autocomplete="list"
                            aria-controls="actSuggestions"
                            aria-expanded="false"
                        >

                    </label>

                    <!-- Autocomplete results -->

                    <div
                        class="act-suggestions"
                        id="actSuggestions"
                        role="listbox"
                        hidden
                    ></div>

                </div>


                <!-- Category filter -->

                <label
                    class="act-category-select"
                    for="actCategory"
                >

                    Catégorie

                    <select id="actCategory">

                        <option value="">
                            Toutes les catégories
                        </option>

                        <?php foreach ($categories as $c): ?>

                            <option value="<?= actEsc($c) ?>">
                                <?= actEsc($c) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </label>

            </div>


            <!-- ======================================== -->
            <!-- 10. ARTICLES GRID                       -->
            <!-- ======================================== -->

            <div class="act-grid" id="actGrid">

                <?php foreach ($articles as $a): ?>

                    <article
                        class="act-card"
                        data-category="<?= actEsc($a['categorie']) ?>"
                        data-search="<?= actEsc(
                            mb_strtolower(
                                $a['titre'] . ' ' .
                                $a['resume'] . ' ' .
                                $a['categorie'],
                                'UTF-8'
                            )
                        ) ?>"
                    >

                        <!-- Article icon and category -->

                        <div class="act-card-top">

                            <div class="act-card-icon">

                                <i class="<?= actEsc($a['icon']) ?>"></i>

                            </div>

                            <span class="act-category">
                                <?= actEsc($a['categorie']) ?>
                            </span>

                        </div>


                        <!-- Publication date -->

                        <div class="act-date">

                            <i class="far fa-calendar"></i>

                            <?= actEsc($prettyDate($a['date'])) ?>

                            <span>·</span>

                            <?= actEsc($a['lecture']) ?>
                            de lecture

                        </div>


                        <!-- Article text -->

                        <h3>
                            <?= actEsc($a['titre']) ?>
                        </h3>

                        <p>
                            <?= actEsc($a['resume']) ?>
                        </p>


                        <!-- Read article -->

                        <a href="?id=<?= (int)$a['id'] ?>">

                            Lire la suite

                            <i class="fas fa-arrow-right"></i>

                        </a>

                    </article>

                <?php endforeach; ?>

            </div>


            <!-- ======================================== -->
            <!-- 11. EMPTY RESULTS MESSAGE               -->
            <!-- ======================================== -->

            <div
                class="act-empty"
                id="actEmpty"
                <?= count($articles) ? 'hidden' : '' ?>
            >

                <i class="fas fa-search"></i>

                <h3>Aucun résultat</h3>

                <p>
                    Aucune actualité publiée pour le moment,
                    ou aucun résultat pour ces filtres.
                </p>

            </div>

        <?php endif; ?>

    </section>

</main>


<!-- ======================================== -->
<!-- 12. FOOTER AND SCRIPTS                    -->
<!-- ======================================== -->

<?php
require __DIR__ . '/../includes/footer.php';
require __DIR__ . '/../includes/scripts.php';
?>


<!-- ======================================== -->
<!-- 13. ARTICLE SEARCH JAVASCRIPT            -->
<!-- ======================================== -->

<?php if (!$selected): ?>

<script>
(function () {

    // ----------------------------------------
    // DOM elements
    // ----------------------------------------

    const input = document.getElementById('actSearch');

    const category = document.getElementById('actCategory');

    const cards = Array.from(
        document.querySelectorAll('#actGrid .act-card')
    );

    const counter = document.getElementById('actCount');

    const empty = document.getElementById('actEmpty');

    const suggestions = document.getElementById('actSuggestions');

    let activeSuggestion = -1;


    // ----------------------------------------
    // Normalize search terms
    // ----------------------------------------

    const normalize = s => s
        .toLocaleLowerCase('fr')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();


    // ----------------------------------------
    // Filter articles by category and search
    // ----------------------------------------

    function filter() {

        const q = normalize(input.value);

        const c = category.value;

        let count = 0;

        cards.forEach(card => {

            const matchesCategory =
                !c || card.dataset.category === c;

            const matchesSearch =
                !q || normalize(card.dataset.search).includes(q);

            const ok = matchesCategory && matchesSearch;

            card.hidden = !ok;

            if (ok) {
                count++;
            }

        });

        counter.textContent =
            count + ' article' + (count !== 1 ? 's' : '');

        empty.hidden = count > 0;
    }


    // ----------------------------------------
    // Close autocomplete suggestions
    // ----------------------------------------

    function closeSuggestions() {

        suggestions.hidden = true;

        suggestions.replaceChildren();

        activeSuggestion = -1;

        input.setAttribute('aria-expanded', 'false');
    }


    // ----------------------------------------
    // Generate autocomplete suggestions
    // ----------------------------------------

    function suggest() {

        closeSuggestions();

        const q = normalize(input.value);

        if (q.length < 2) {
            return;
        }

        const options = [];

        const seen = new Set();

        cards.forEach(card => {

            const title =
                card.querySelector('h3')?.textContent?.trim() || '';

            const cat = card.dataset.category || '';


            // Article title suggestions

            if (
                normalize(title).includes(q) &&
                !seen.has('t' + title)
            ) {

                options.push({
                    label: title,
                    kind: 'Article',
                    url: card.querySelector('a[href]')
                        ?.getAttribute('href')
                });

                seen.add('t' + title);
            }


            // Category suggestions

            if (
                normalize(cat).includes(q) &&
                !seen.has('c' + cat)
            ) {

                options.push({
                    label: cat,
                    kind: 'Catégorie'
                });

                seen.add('c' + cat);
            }

        });


        // Display up to six suggestions

        options.slice(0, 6).forEach(option => {

            const b = document.createElement('button');

            b.type = 'button';

            b.setAttribute('role', 'option');


            const name = document.createElement('span');

            name.textContent = option.label;


            const meta = document.createElement('small');

            meta.textContent = option.kind;


            b.append(name, meta);


            // Prevent losing input focus
            b.addEventListener('mousedown', e => {
                e.preventDefault();
            });


            // Handle suggestion click
            b.addEventListener('click', () => {

                if (
                    option.kind === 'Article' &&
                    option.url
                ) {

                    window.location.href = option.url;

                    return;
                }

                input.value = '';

                category.value = option.label;

                filter();

                closeSuggestions();

            });

            suggestions.appendChild(b);

        });


        suggestions.hidden = !suggestions.children.length;

        input.setAttribute(
            'aria-expanded',
            String(!suggestions.hidden)
        );
    }


    // ----------------------------------------
    // Search field events
    // ----------------------------------------

    input.addEventListener('input', () => {

        filter();

        suggest();

    });


    // ----------------------------------------
    // Category filter events
    // ----------------------------------------

    category.addEventListener('change', () => {

        closeSuggestions();

        filter();

    });


    // ----------------------------------------
    // Keyboard navigation
    // ----------------------------------------

    input.addEventListener('keydown', e => {

        const buttons = Array.from(
            suggestions.querySelectorAll('button')
        );

        if (e.key === 'Escape') {

            closeSuggestions();

            return;
        }

        if (!buttons.length) {
            return;
        }


        // Arrow navigation
        if (
            e.key === 'ArrowDown' ||
            e.key === 'ArrowUp'
        ) {

            e.preventDefault();

            activeSuggestion = (
                activeSuggestion +
                (e.key === 'ArrowDown' ? 1 : -1) +
                buttons.length
            ) % buttons.length;


            buttons.forEach((b, i) => {

                b.classList.toggle(
                    'is-active',
                    i === activeSuggestion
                );

            });

        } else if (
            e.key === 'Enter' &&
            activeSuggestion >= 0
        ) {

            e.preventDefault();

            buttons[activeSuggestion].click();
        }

    });


    // ----------------------------------------
    // Close suggestions when clicking outside
    // ----------------------------------------

    document.addEventListener('click', e => {

        if (!e.target.closest('.act-search-wrap')) {

            closeSuggestions();
        }

    });

})();
</script>

<?php endif; ?>

</body>
</html>
