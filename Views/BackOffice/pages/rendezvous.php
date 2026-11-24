
<?php
if (!defined("CIVLINK_BACKOFFICE_RENDER")) {
    http_response_code(403);
    exit("Accès direct interdit.");
}
?>

<style>
/* ===== AUTOCOMPLETE LIEU - BACK OFFICE ===== */

.rdv-lieu-wrapper {
    position: relative;
    min-width: 0;
}

.rdv-lieu-wrapper input {
    width: 100%;
}

#rdvLieuSuggestions {
    position: absolute;
    top: calc(100% + 5px);
    left: 0;
    right: 0;
    z-index: 9999;
    max-height: 250px;
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid #d8e1eb;
    border-radius: 12px;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.20);
}

#rdvLieuSuggestions[hidden],
#rdvLieuSuggestions:empty {
    display: none;
}

#rdvLieuSuggestions button {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 12px 15px;
    border: none;
    background: transparent;
    color: #223143;
    text-align: left;
    cursor: pointer;
    font-size: 14px;
}

#rdvLieuSuggestions button i {
    color: #F58232;
    flex-shrink: 0;
}

#rdvLieuSuggestions button:hover,
#rdvLieuSuggestions button:focus-visible {
    background: #FFF0E1;
    outline: none;
}

#rdvLieuSuggestions .rdv-suggestion-message {
    padding: 12px 15px;
    font-size: 13px;
    color: #718095;
}

/* Mode sombre */

[data-theme="dark"] #rdvLieuSuggestions,
.rdv-admin.theme-dark #rdvLieuSuggestions {
    background: #1C2C3D;
    border-color: #344457;
}

[data-theme="dark"] #rdvLieuSuggestions button,
.rdv-admin.theme-dark #rdvLieuSuggestions button {
    color: #ECF3FA;
}

[data-theme="dark"] #rdvLieuSuggestions button:hover,
[data-theme="dark"] #rdvLieuSuggestions button:focus-visible,
.rdv-admin.theme-dark #rdvLieuSuggestions button:hover {
    background: #30465B;
}

[data-theme="dark"] #rdvLieuSuggestions .rdv-suggestion-message {
    color: #AFBDCD;
}
</style>

<div class="rdv-page rdv-admin">
    <div class="container-xl rdv-content">

        <!-- ===== EN-TÊTE ===== -->

        <nav class="rdv-crumb">
            <a href="<?= be($base) ?>/index.php">Accueil</a>
            › Back Office
        </nav>

        <div class="rdv-kicker">
            BACK OFFICE CIVLINK
        </div>

        <h1>Gestion des rendez-vous</h1>

        <p>
            Créneaux horaires, réservations et historique
            de démonstration.
        </p>

        <a class="rdv-secondary"
           href="/CivLink/Views/FrontOffice/pages/rendezvous.php">
            Voir le Front Office
        </a>

        <?php if (false): ?>

            <div class="rdv-error">
                Accès réservé aux administrateurs.
                <a href="/CivLink/Views/FrontOffice/pages/authentification.php">
                    Se connecter
                </a>
            </div>

        <?php else: ?>

            <?php if ($error): ?>
                <div class="rdv-error" role="alert">
                    <?= be($error) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['ok'])): ?>
                <div class="rdv-success">
                    Modification enregistrée.
                </div>
            <?php endif; ?>

            <!-- ===== CRUD CRÉNEAUX HORAIRES ===== -->

            <section class="rdv-panel">

                <h2>
                    <?= $edit
                        ? 'Modifier un créneau'
                        : 'Ajouter un créneau horaire' ?>
                </h2>

                <form class="rdv-form" method="post">

                    <input
                        type="hidden"
                        name="csrf"
                        value="<?= be(authToken()) ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="<?= $edit ? 'slot_edit' : 'slot_add' ?>"
                    >

                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int)($edit['idCreneau'] ?? 0) ?>"
                    >

                    <div class="rdv-grid">

                        <!-- Service -->

                        <label>
                            Service *

                            <input
                                name="serviceNom"
                                required
                                maxlength="120"
                                value="<?= be($edit['serviceNom'] ?? '') ?>"
                                placeholder="État civil"
                            >
                        </label>

                        <!-- Lieu avec autocomplete -->

                        <div class="rdv-lieu-wrapper">

                            <label for="rdvLieu">
                                Lieu *
                            </label>

                            <input
                                type="text"
                                id="rdvLieu"
                                name="lieu"
                                required
                                maxlength="180"
                                value="<?= be($edit['lieu'] ?? '') ?>"
                                placeholder="Ex. : Tunis, Ariana, Sfax..."
                                autocomplete="off"
                                aria-autocomplete="list"
                                aria-controls="rdvLieuSuggestions"
                                aria-expanded="false"
                            >

                            <div
                                id="rdvLieuSuggestions"
                                role="listbox"
                                hidden
                            ></div>

                        </div>

                        <!-- Date -->

                        <label>
                            Date *

                            <input
                                type="date"
                                name="dateCreneau"
                                min="<?= date('Y-m-d') ?>"
                                required
                                value="<?= be($edit['dateCreneau'] ?? '') ?>"
                            >
                        </label>

                        <!-- Heure début -->

                        <label>
                            Début *

                            <input
                                type="time"
                                name="heureDebut"
                                required
                                value="<?= be(
                                    isset($edit['heureDebut'])
                                        ? substr($edit['heureDebut'], 0, 5)
                                        : ''
                                ) ?>"
                            >
                        </label>

                        <!-- Heure fin -->

                        <label>
                            Fin *

                            <input
                                type="time"
                                name="heureFin"
                                required
                                value="<?= be(
                                    isset($edit['heureFin'])
                                        ? substr($edit['heureFin'], 0, 5)
                                        : ''
                                ) ?>"
                            >
                        </label>

                        <!-- Statut -->

                        <label>
                            Statut

                            <select name="actif">

                                <option
                                    value="1"
                                    <?= ($edit['actif'] ?? 1)
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Actif
                                </option>

                                <option
                                    value="0"
                                    <?= !($edit['actif'] ?? 1)
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Désactivé
                                </option>

                            </select>
                        </label>

                    </div>

                    <button class="rdv-primary" type="submit">
                        <?= $edit
                            ? 'Enregistrer'
                            : 'Ajouter le créneau' ?>
                    </button>

                    <?php if ($edit): ?>

                        <a
                            class="rdv-secondary"
                            href="index.php?page=rendezvous"
                        >
                            Annuler
                        </a>

                    <?php endif; ?>

                </form>

            </section>

            <!-- ===== LISTE DES CRÉNEAUX ===== -->

            <section class="rdv-panel">

                <h2>
                    Créneaux horaires (<?= count($slots) ?>)
                </h2>

                <div class="rdv-table-scroll">

                    <table class="rdv-table">

                        <thead>
                            <tr>
                                <th>Service / lieu</th>
                                <th>Date</th>
                                <th>Horaire</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($slots as $s): ?>

                                <tr>

                                    <td>
                                        <strong>
                                            <?= be($s['serviceNom']) ?>
                                        </strong>

                                        <small>
                                            <?= be($s['lieu']) ?>
                                        </small>
                                    </td>

                                    <td>
                                        <?= be($s['dateCreneau']) ?>
                                    </td>

                                    <td>
                                        <?= be(
                                            substr($s['heureDebut'], 0, 5) .
                                            ' – ' .
                                            substr($s['heureFin'], 0, 5)
                                        ) ?>
                                    </td>

                                    <td>
                                        <?php
                                        if ((int)$s['reserve']) {
                                            echo 'Réservé';
                                        } elseif ((int)$s['actif']) {
                                            echo 'Libre';
                                        } else {
                                            echo 'Désactivé';
                                        }
                                        ?>
                                    </td>

                                    <td>

                                        <div class="rdv-admin-actions">

                                            <a
                                                class="rdv-secondary"
                                                href="index.php?page=rendezvous&amp;edit=<?= (int)$s['idCreneau'] ?>"
                                            >
                                                Modifier
                                            </a>

                                            <form
                                                method="post"
                                                onsubmit="return confirm('Supprimer ce créneau ? Les créneaux avec historique ne peuvent pas être supprimés.')"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="csrf"
                                                    value="<?= be(authToken()) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="slot_delete"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int)$s['idCreneau'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="rdv-danger"
                                                >
                                                    Supprimer
                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </section>

            <!-- ===== GESTION DES RÉSERVATIONS ===== -->

            <section class="rdv-panel">

                <h2>
                    Réservations (<?= count($bookings) ?>)
                </h2>

                <div class="rdv-table-scroll">

                    <table class="rdv-table">

                        <thead>
                            <tr>
                                <th>Citoyen</th>
                                <th>Rendez-vous</th>
                                <th>Motif</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($bookings as $b): ?>

                                <tr>

                                    <!-- Citoyen -->

                                    <td>
                                        <?= be(
                                            $b['prenom'] . ' ' .
                                            $b['nom']
                                        ) ?>

                                        <small>
                                            <?= be($b['email']) ?>
                                        </small>
                                    </td>

                                    <!-- Rendez-vous -->

                                    <td>
                                        <?= be(
                                            $b['serviceNom'] .
                                            ' — ' .
                                            $b['lieu']
                                        ) ?>

                                        <small>
                                            <?= be(
                                                $b['dateCreneau'] .
                                                ' ' .
                                                $b['heureDebut']
                                            ) ?>
                                        </small>
                                    </td>

                                    <!-- Motif -->

                                    <td>
                                        <?= be($b['motif']) ?>
                                    </td>

                                    <!-- Statut -->

                                    <td>
                                        <?= be($b['statut']) ?>
                                    </td>

                                    <!-- Actions -->

                                    <td>

                                        <div class="rdv-admin-actions">

                                            <?php if (
                                                $b['statut'] === 'confirme'
                                            ): ?>

                                                <form
                                                    method="post"
                                                    onsubmit="return confirm('Annuler cette réservation ?')"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="csrf"
                                                        value="<?= be(authToken()) ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="action"
                                                        value="booking_cancel"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?= (int)$b['idRendezVous'] ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="rdv-secondary"
                                                    >
                                                        Annuler
                                                    </button>

                                                </form>

                                            <?php else: ?>

                                                <form
                                                    method="post"
                                                    onsubmit="return confirm('Supprimer définitivement cette réservation annulée ?')"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="csrf"
                                                        value="<?= be(authToken()) ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="action"
                                                        value="booking_delete"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?= (int)$b['idRendezVous'] ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="rdv-danger"
                                                    >
                                                        Supprimer
                                                    </button>

                                                </form>

                                            <?php endif; ?>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </section>

        <?php endif; ?>

    </div>
</div>

<!-- ===== AUTOCOMPLETE PHOTON / OPENSTREETMAP ===== -->

<script>
(function () {
    'use strict';

    const input = document.getElementById('rdvLieu');
    const box = document.getElementById('rdvLieuSuggestions');

    if (!input || !box) return;

    let timer = null;
    let controller = null;
    let requestId = 0;

    function closeSuggestions() {
        box.replaceChildren();
        box.hidden = true;
        input.setAttribute('aria-expanded', 'false');
    }

    function showMessage(message) {
        box.replaceChildren();

        const div = document.createElement('div');
        div.className = 'rdv-suggestion-message';
        div.textContent = message;

        box.appendChild(div);
        box.hidden = false;

        input.setAttribute('aria-expanded', 'true');
    }

    function normalize(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    // Rechercher les lieux en Tunisie
    input.addEventListener('input', function () {

        clearTimeout(timer);
        requestId++;

        if (controller) {
            controller.abort();
        }

        closeSuggestions();

        const query = input.value.trim();

        if (query.length < 3) return;

        const currentRequest = requestId;

        timer = setTimeout(async function () {

            controller = new AbortController();

            try {

                const url =
                    'https://photon.komoot.io/api/?q=' +
                    encodeURIComponent(query) +
                    '&countrycode=TN&limit=6&lang=fr';

                const response = await fetch(url, {
                    signal: controller.signal
                });

                if (!response.ok) {
                    throw new Error('Service indisponible');
                }

                const data = await response.json();

                // Éviter les anciennes réponses
                if (currentRequest !== requestId) return;

                closeSuggestions();

                const unique = new Set();
                let count = 0;

                (data.features || []).forEach(function (place) {

                    const p = place.properties || {};

                    // Uniquement les résultats tunisiens
                    if (
                        p.countrycode &&
                        p.countrycode.toUpperCase() !== 'TN'
                    ) {
                        return;
                    }

                    const parts = [
                        p.name,
                        p.street,
                        p.city,
                        p.state
                    ].filter(Boolean);

                    const label = [...new Set(parts)].join(', ');

                    if (!label) return;

                    const normalized = normalize(label);

                    if (unique.has(normalized)) return;

                    unique.add(normalized);

                    const button = document.createElement('button');

                    button.type = 'button';
                    button.setAttribute('role', 'option');

                    const icon = document.createElement('i');
                    icon.className = 'fas fa-map-marker-alt';

                    const text = document.createElement('span');
                    text.textContent = label;

                    button.append(icon, text);

                    button.addEventListener('click', function () {
                        input.value = label;
                        requestId++;
                        clearTimeout(timer);

                        if (controller) controller.abort();

                        closeSuggestions();
                    });

                    box.appendChild(button);
                    count++;
                });

                if (count === 0) {
                    showMessage('Aucun lieu trouvé en Tunisie.');
                } else {
                    box.hidden = false;
                    input.setAttribute('aria-expanded', 'true');
                }

            } catch (error) {

                if (error.name === 'AbortError') return;

                if (currentRequest !== requestId) return;

                showMessage(
                    'Suggestions indisponibles. Saisissez le lieu manuellement.'
                );
            }

        }, 450);
    });

    // Navigation clavier
    input.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {
            closeSuggestions();
        }

        if (
            event.key === 'ArrowDown' &&
            !box.hidden
        ) {
            const first = box.querySelector('button');

            if (first) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    box.addEventListener('keydown', function (event) {

        const buttons = [...box.querySelectorAll('button')];
        const index = buttons.indexOf(document.activeElement);

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            buttons[Math.min(index + 1, buttons.length - 1)]?.focus();
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();

            if (index <= 0) {
                input.focus();
            } else {
                buttons[index - 1].focus();
            }
        }

        if (event.key === 'Escape') {
            closeSuggestions();
            input.focus();
        }
    });

    // Fermer en cliquant ailleurs
    document.addEventListener('click', function (event) {

        if (
            !input.contains(event.target) &&
            !box.contains(event.target)
        ) {
            closeSuggestions();
        }
    });

})();
</script>
