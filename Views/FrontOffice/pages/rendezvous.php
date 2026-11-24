
<?php
require_once __DIR__ . '/../../../Models/Auth.php';

authStart();
header('Cache-Control: private, no-store');

$pageTitle = 'Rendez-vous - CIVLINK';
$currentPage = 'rendezvous';
$base = '/CivLink/Views/FrontOffice';
$assets = $base . '/assets';

function rdve($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function rdvDate($value): string {
    return date('d/m/Y', strtotime($value));
}

$uid = (int)($_SESSION['user']['id'] ?? 0);
$error = '';
$slots = [];
$my = [];
$edit = null;

try {
    $db = authDb();

    // ===== ACTIONS DE RESERVATION =====

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (!$uid) {
            throw new RuntimeException(
                'Connectez-vous pour réserver un rendez-vous.'
            );
        }

        if (!authCheck()) {
            throw new RuntimeException(
                'Formulaire invalide : rechargez la page.'
            );
        }

        $action = authPost('action');
        $id = (int)authPost('id');

        if ($action === 'reserve' || $action === 'change') {

            $slot = (int)authPost('idCreneau');
            $motif = trim(authPost('motif'));

            if ($slot < 1 ||
                mb_strlen($motif) < 3 ||
                mb_strlen($motif) > 240) {

                throw new RuntimeException(
                    'Choisissez un créneau et précisez le motif.'
                );
            }

            $db->beginTransaction();

            $q = $db->prepare(
                'SELECT idCreneau
                 FROM creneau_horaire
                 WHERE idCreneau = ?
                 AND actif = 1
                 AND TIMESTAMP(dateCreneau, heureDebut) > NOW()
                 FOR UPDATE'
            );

            $q->execute([$slot]);

            if (!$q->fetchColumn()) {
                throw new RuntimeException(
                    'Ce créneau est indisponible ou passé.'
                );
            }

            if ($action === 'change') {

                $q = $db->prepare(
                    "SELECT idCreneau
                     FROM rendez_vous
                     WHERE idRendezVous = ?
                     AND idUtilisateur = ?
                     AND statut = 'confirme'
                     FOR UPDATE"
                );

                $q->execute([$id, $uid]);

                if (!$q->fetchColumn()) {
                    throw new RuntimeException(
                        'Ce rendez-vous ne peut pas être modifié.'
                    );
                }

                $q = $db->prepare(
                    'UPDATE rendez_vous
                     SET idCreneau = ?, motif = ?
                     WHERE idRendezVous = ?
                     AND idUtilisateur = ?'
                );

                $q->execute([$slot, $motif, $id, $uid]);

            } else {

                $q = $db->prepare(
                    'INSERT INTO rendez_vous
                     (idUtilisateur, idCreneau, motif)
                     VALUES (?, ?, ?)'
                );

                $q->execute([$uid, $slot, $motif]);
            }

            $db->commit();

        } elseif ($action === 'cancel') {

            $q = $db->prepare(
                "UPDATE rendez_vous r
                 JOIN creneau_horaire c
                 ON c.idCreneau = r.idCreneau
                 SET r.statut = 'annule'
                 WHERE r.idRendezVous = ?
                 AND r.idUtilisateur = ?
                 AND r.statut = 'confirme'
                 AND TIMESTAMP(c.dateCreneau, c.heureDebut) > NOW()"
            );

            $q->execute([$id, $uid]);

            if (!$q->rowCount()) {
                throw new RuntimeException(
                    'Impossible d’annuler ce rendez-vous.'
                );
            }

        } else {
            throw new RuntimeException('Action inconnue.');
        }

        header(
            'Location: rendezvous.php?ok=1#mes-rdv',
            true,
            303
        );
        exit;
    }

    // ===== CRENEAUX DISPONIBLES =====

    $q = $db->query(
        "SELECT
            c.idCreneau,
            c.serviceNom,
            c.lieu,
            c.dateCreneau,
            c.heureDebut,
            c.heureFin
         FROM creneau_horaire c
         LEFT JOIN rendez_vous r
           ON r.idCreneau = c.idCreneau
           AND r.statut = 'confirme'
         WHERE c.actif = 1
           AND TIMESTAMP(c.dateCreneau, c.heureDebut) > NOW()
           AND r.idRendezVous IS NULL
         ORDER BY c.dateCreneau, c.heureDebut
         LIMIT 250"
    );

    $slots = $q->fetchAll(PDO::FETCH_ASSOC);

    // ===== MES RENDEZ-VOUS =====

    if ($uid) {

        $q = $db->prepare(
            'SELECT
                r.idRendezVous,
                r.motif,
                r.statut,
                c.idCreneau,
                c.serviceNom,
                c.lieu,
                c.dateCreneau,
                c.heureDebut,
                c.heureFin
             FROM rendez_vous r
             JOIN creneau_horaire c
               ON c.idCreneau = r.idCreneau
             WHERE r.idUtilisateur = ?
             ORDER BY c.dateCreneau DESC, c.heureDebut DESC'
        );

        $q->execute([$uid]);
        $my = $q->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $ex) {

    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    $error = $ex->getCode() === '23000'
        ? 'Ce créneau vient d’être réservé. Choisissez-en un autre.'
        : 'Erreur MySQL : vérifiez database_rendezvous.sql.';

} catch (RuntimeException $ex) {

    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    $error = $ex->getMessage();
}

// ===== MODIFICATION =====

if (isset($_GET['edit'])) {

    foreach ($my as $v) {

        if ((int)$v['idRendezVous'] === (int)$_GET['edit']
            && $v['statut'] === 'confirme'
            && strtotime(
                $v['dateCreneau'] . ' ' . $v['heureDebut']
            ) > time()) {

            $edit = $v;
            break;
        }
    }
}

// ===== LIEUX DISPONIBLES =====

$lieux = [];

foreach ($slots as $s) {
    if (trim($s['lieu']) !== '') {
        $lieux[] = $s['lieu'];
    }
}

$lieux = array_values(array_unique($lieux));
sort($lieux, SORT_NATURAL | SORT_FLAG_CASE);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php require __DIR__ . '/../includes/head.php'; ?>

    <link rel="stylesheet"
          href="<?= rdve($assets) ?>/css/rendezvous.css">

    <style>
        /* ===== AUTOCOMPLETE LIEU ===== */

        .rdv-lieu-wrap {
            position: relative;
            z-index: 20;
        }

        .rdv-lieu-wrap input {
            width: 100%;
        }

        #rdvSuggestions {
            position: absolute;
            top: calc(100% + 5px);
            left: 0;
            right: 0;
            z-index: 9999;
            max-height: 230px;
            overflow-y: auto;
            border: 1px solid #d8e1eb;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 12px 25px rgba(0,0,0,.18);
        }

        #rdvSuggestions:empty,
        #rdvSuggestions[hidden] {
            display: none;
        }

        #rdvSuggestions button {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 13px 16px;
            background: transparent;
            color: #223143;
            border: 0;
            text-align: left;
            cursor: pointer;
            font-size: 14px;
        }

        #rdvSuggestions button i {
            color: #f58232;
        }

        #rdvSuggestions button:hover,
        #rdvSuggestions button:focus-visible {
            background: #fff0e1;
            outline: none;
        }

        .rdv-lieu-info {
            display: block;
            margin-top: 7px;
            color: #718095;
            font-size: 12px;
        }

        [data-theme="dark"] #rdvSuggestions {
            background: #1c2c3d;
            border-color: #344457;
        }

        [data-theme="dark"] #rdvSuggestions button {
            color: #ecf3fa;
        }

        [data-theme="dark"] #rdvSuggestions button:hover,
        [data-theme="dark"] #rdvSuggestions button:focus-visible {
            background: #30465b;
        }

        [data-theme="dark"] .rdv-lieu-info {
            color: #afbdcd;
        }
    </style>
</head>

<body>

<?php require __DIR__ . '/../includes/navbar.php'; ?>

<main class="rdv-page">

    <!-- ===== HERO ===== -->

    <section class="rdv-hero">
        <div class="container-xl">

            <nav class="rdv-crumb">
                <a href="<?= rdve($base) ?>/index.php">
                    Accueil
                </a>
                › Rendez-vous
            </nav>

            <div class="rdv-kicker">
                SERVICES PUBLICS EN LIGNE
            </div>

            <h1>
                Vos rendez-vous,
                <span>en toute simplicité.</span>
            </h1>

            <p>
                Consultez les créneaux disponibles,
                réservez une visite et retrouvez vos
                demandes dans votre espace CIVLINK.
            </p>

            <a class="rdv-primary" href="#reserver">
                Réserver un rendez-vous
                <i class="fas fa-arrow-right"></i>
            </a>

        </div>
    </section>

    <div class="container-xl rdv-content">

        <div class="rdv-note">
            Prototype CIVLINK : les créneaux proposés sont
            fictifs. Aucune administration réelle n’est contactée.
        </div>

        <?php if ($error): ?>
            <div class="rdv-error" role="alert">
                <?= rdve($error) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['ok']) && !$error): ?>
            <div class="rdv-success" role="status">
                Votre demande a été enregistrée.
            </div>
        <?php endif; ?>

        <!-- ===== RESERVATION ===== -->

        <section class="rdv-panel" id="reserver">

            <div class="rdv-section-head">
                <div>
                    <div class="rdv-kicker">
                        PRÉPARER VOTRE VISITE
                    </div>

                    <h2>
                        <?= $edit
                            ? 'Modifier mon rendez-vous'
                            : 'Réserver un rendez-vous' ?>
                    </h2>
                </div>

                <span>
                    <?= count($slots) ?> créneaux disponibles
                </span>
            </div>

            <?php if (!$uid): ?>

                <p>
                    Connectez-vous pour réserver et gérer
                    vos rendez-vous.
                </p>

                <a class="rdv-primary"
                   href="<?= rdve($base) ?>/pages/authentification.php">
                    Se connecter
                </a>

            <?php else: ?>

                <form class="rdv-form" method="post">

                    <input type="hidden"
                           name="csrf"
                           value="<?= rdve(authToken()) ?>">

                    <input type="hidden"
                           name="action"
                           value="<?= $edit ? 'change' : 'reserve' ?>">

                    <input type="hidden"
                           name="id"
                           value="<?= (int)($edit['idRendezVous'] ?? 0) ?>">

                    <!-- ===== LIEU AVEC AUTOCOMPLETE ===== -->

                    <div class="rdv-lieu-wrap">

                        <label for="rdvLieu">
                            Lieu du rendez-vous
                        </label>

                        <input
                            type="text"
                            id="rdvLieu"
                            placeholder="Rechercher une ville ou un lieu..."
                            autocomplete="off"
                            aria-autocomplete="list"
                            aria-controls="rdvSuggestions"
                            aria-expanded="false"
                        >

                        <div id="rdvSuggestions"
                             role="listbox"
                             hidden></div>

                        <small class="rdv-lieu-info"
                               id="rdvLieuInfo">
                            Recherchez parmi les lieux ayant
                            des créneaux disponibles.
                        </small>

                    </div>

                    <div class="rdv-grid">

                        <label>
                            Choisir un créneau *

                            <select name="idCreneau"
                                    id="rdvCreneau"
                                    required>

                                <option value="">
                                    Sélectionner un créneau disponible
                                </option>

                                <?php foreach ($slots as $s): ?>

                                    <option
                                        value="<?= (int)$s['idCreneau'] ?>">

                                        <?= rdve(
                                            $s['serviceNom'] .
                                            ' — ' . $s['lieu'] .
                                            ' — ' . rdvDate($s['dateCreneau']) .
                                            ' à ' .
                                            substr($s['heureDebut'], 0, 5)
                                        ) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>
                        </label>

                        <label>
                            Motif de la visite *

                            <input
                                name="motif"
                                required
                                minlength="3"
                                maxlength="240"
                                value="<?= rdve($edit['motif'] ?? '') ?>"
                                placeholder="Ex. : Demande de renseignements"
                            >
                        </label>

                    </div>

                    <?php if ($edit): ?>

                        <p class="rdv-help">
                            Choisissez un autre créneau disponible
                            pour modifier votre rendez-vous.
                        </p>

                    <?php endif; ?>

                    <button class="rdv-primary"
                            type="submit"
                            id="rdvSubmit"
                            <?= $slots ? '' : 'disabled' ?>>

                        <?= $edit
                            ? 'Enregistrer les modifications'
                            : 'Confirmer la réservation' ?>

                        <i class="fas fa-check"></i>
                    </button>

                    <?php if ($edit): ?>

                        <a class="rdv-secondary"
                           href="rendezvous.php#mes-rdv">
                            Annuler la modification
                        </a>

                    <?php endif; ?>

                </form>

            <?php endif; ?>

        </section>

        <!-- ===== MES RENDEZ-VOUS ===== -->

        <section class="rdv-panel" id="mes-rdv">

            <div class="rdv-section-head">
                <div>
                    <div class="rdv-kicker">
                        MON ESPACE CITOYEN
                    </div>

                    <h2>Mes rendez-vous</h2>
                </div>

                <span>
                    <?= count($my) ?> réservation(s)
                </span>
            </div>

            <?php if (!$my): ?>
                <div class="rdv-empty">
                    Aucun rendez-vous pour le moment.
                </div>
            <?php endif; ?>

            <div class="rdv-cards">

                <?php foreach ($my as $r): ?>

                    <article class="rdv-card">

                        <div class="rdv-card-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>

                        <div class="rdv-card-main">

                            <strong>
                                <?= rdve($r['serviceNom']) ?>
                            </strong>

                            <p><?= rdve($r['lieu']) ?></p>

                            <small>
                                <?= rdve(
                                    rdvDate($r['dateCreneau']) .
                                    ' · ' .
                                    substr($r['heureDebut'], 0, 5) .
                                    '–' .
                                    substr($r['heureFin'], 0, 5)
                                ) ?>
                            </small>

                            <p>
                                Motif : <?= rdve($r['motif']) ?>
                            </p>

                            <span class="rdv-status <?= $r['statut'] === 'confirme'
                                ? 'is-confirmed'
                                : 'is-cancelled' ?>">

                                <?= $r['statut'] === 'confirme'
                                    ? 'Confirmé'
                                    : 'Annulé' ?>

                            </span>

                        </div>

                        <?php if (
                            $r['statut'] === 'confirme' &&
                            strtotime(
                                $r['dateCreneau'] . ' ' .
                                $r['heureDebut']
                            ) > time()
                        ): ?>

                            <div class="rdv-card-actions">

                                <a class="rdv-secondary"
                                   href="?edit=<?= (int)$r['idRendezVous'] ?>#reserver">
                                    Modifier
                                </a>

                                <form method="post"
                                      onsubmit="return confirm('Annuler ce rendez-vous ?')">

                                    <input type="hidden"
                                           name="csrf"
                                           value="<?= rdve(authToken()) ?>">

                                    <input type="hidden"
                                           name="action"
                                           value="cancel">

                                    <input type="hidden"
                                           name="id"
                                           value="<?= (int)$r['idRendezVous'] ?>">

                                    <button type="submit"
                                            class="rdv-danger">
                                        Annuler
                                    </button>

                                </form>

                            </div>

                        <?php endif; ?>

                    </article>

                <?php endforeach; ?>

            </div>

        </section>

    </div>

</main>

<?php
require __DIR__ . '/../includes/footer.php';
require __DIR__ . '/../includes/scripts.php';
?>

<!-- ===== AUTOCOMPLETE ET FILTRAGE DES CRENEAUX ===== -->

<script>
(function () {
    'use strict';

    const input = document.getElementById('rdvLieu');
    const suggestions = document.getElementById('rdvSuggestions');
    const select = document.getElementById('rdvCreneau');
    const info = document.getElementById('rdvLieuInfo');
    const submit = document.getElementById('rdvSubmit');

    if (!input || !suggestions || !select) return;

    // Lieux et créneaux provenant de MySQL
    const lieux = <?= json_encode(
        $lieux,
        JSON_UNESCAPED_UNICODE |
        JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT
    ) ?>;

    const creneaux = <?= json_encode(
        array_map(function ($s) {
            return [
                'id' => (int)$s['idCreneau'],
                'lieu' => $s['lieu'],
                'label' => $s['serviceNom'] . ' — ' .
                    $s['lieu'] . ' — ' .
                    rdvDate($s['dateCreneau']) . ' à ' .
                    substr($s['heureDebut'], 0, 5)
            ];
        }, $slots),
        JSON_UNESCAPED_UNICODE |
        JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT
    ) ?>;

    function normalize(text) {
        return String(text || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function fermerSuggestions() {
        suggestions.replaceChildren();
        suggestions.hidden = true;
        input.setAttribute('aria-expanded', 'false');
    }

    // Actualiser les créneaux selon le lieu
    function filtrerCreneaux() {
        const recherche = normalize(input.value);
        const ancienneValeur = select.value;

        select.replaceChildren();

        const placeholder = new Option(
            'Sélectionner un créneau disponible',
            ''
        );

        select.appendChild(placeholder);

        let nombre = 0;

        creneaux.forEach(function (c) {
            if (
                !recherche ||
                normalize(c.lieu).includes(recherche)
            ) {
                select.appendChild(
                    new Option(c.label, String(c.id))
                );
                nombre++;
            }
        });

        if (
            ancienneValeur &&
            [...select.options].some(
                option => option.value === ancienneValeur
            )
        ) {
            select.value = ancienneValeur;
        }

        info.textContent = nombre
            ? nombre + ' créneau(x) disponible(s) pour cette recherche.'
            : 'Aucun créneau disponible pour ce lieu. Essayez une autre ville.';

        if (submit) {
            submit.disabled = nombre === 0;
        }
    }

    // Suggestions de lieux existant dans CIVLINK
    function afficherSuggestions() {
        const recherche = normalize(input.value);

        fermerSuggestions();
        filtrerCreneaux();

        if (recherche.length < 2) return;

        const resultats = lieux
            .filter(lieu =>
                normalize(lieu).includes(recherche)
            )
            .slice(0, 6);

        resultats.forEach(function (lieu) {
            const button = document.createElement('button');

            button.type = 'button';
            button.setAttribute('role', 'option');

            const icon = document.createElement('i');
            icon.className = 'fas fa-map-marker-alt';

            const text = document.createElement('span');
            text.textContent = lieu;

            button.append(icon, text);

            button.addEventListener('click', function () {
                input.value = lieu;
                fermerSuggestions();
                filtrerCreneaux();
            });

            suggestions.appendChild(button);
        });

        if (resultats.length) {
            suggestions.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }
    }

    input.addEventListener('input', afficherSuggestions);

    input.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            fermerSuggestions();
        }

        if (
            event.key === 'Enter' &&
            !suggestions.hidden
        ) {
            const first = suggestions.querySelector('button');

            if (first) {
                event.preventDefault();
                first.click();
            }
        }
    });

    document.addEventListener('click', function (event) {
        if (
            !input.contains(event.target) &&
            !suggestions.contains(event.target)
        ) {
            fermerSuggestions();
        }
    });

    filtrerCreneaux();
})();
</script>

</body>
</html>
