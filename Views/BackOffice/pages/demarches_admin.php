
<?php
if (!defined('CIVLINK_BACKOFFICE_RENDER')) {
    http_response_code(403);
    exit;
}

if (!function_exists('demE')) {
    function demE($v): string {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}
?>

<style>
    .civ-dem {
        color: #243346;
    }

    .dark .civ-dem {
        color: #e5e7eb;
    }

    .civ-dem h1 {
        font-size: 27px;
        font-weight: 750;
        margin-bottom: 8px;
    }

    .civ-dem h2 {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 18px;
    }

    .civ-dem p.lead {
        color: #64748b;
        margin-bottom: 18px;
    }

    .civ-dem .panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 24px;
        margin: 20px 0;
        box-shadow: 0 2px 12px #00000007;
    }

    .dark .civ-dem .panel {
        background: #1f2937;
        border-color: #374151;
    }

    .civ-dem .fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 15px;
    }

    .civ-dem label {
        display: block;
        font-size: 14px;
        font-weight: 600;
    }

    .civ-dem input:not([type=checkbox]),
    .civ-dem textarea,
    .civ-dem select {
        display: block;
        width: 100%;
        margin-top: 6px;
        padding: 11px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        color: #17253b;
        font-weight: 400;
    }

    .dark .civ-dem input:not([type=checkbox]),
    .dark .civ-dem textarea,
    .dark .civ-dem select {
        background: #111827;
        color: white;
        border-color: #475569;
    }

    .civ-dem .wide {
        grid-column: 1 / -1;
    }

    .civ-dem .button {
        display: inline-block;
        background: #f7941d;
        color: white;
        padding: 11px 18px;
        border: 0;
        border-radius: 9px;
        font-weight: 700;
        cursor: pointer;
        margin-top: 15px;
        text-decoration: none;
    }

    .civ-dem .secondary {
        background: #eef1f5;
        color: #263449;
        margin-left: 7px;
    }

    .civ-dem .danger {
        background: #b91c1c;
    }

    .civ-dem .small {
        padding: 7px 10px;
        margin: 0 5px 0 0;
        font-size: 12px;
    }

    .civ-dem .scroll {
        overflow: auto;
    }

    .civ-dem table {
        width: 100%;
        border-collapse: collapse;
        min-width: 730px;
    }

    .civ-dem th,
    .civ-dem td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .dark .civ-dem th,
    .dark .civ-dem td {
        border-color: #374151;
    }

    .civ-dem th {
        font-size: 13px;
        color: #64748b;
    }

    .civ-dem form.inline {
        display: inline;
    }

    .civ-dem .notice {
        padding: 12px 16px;
        border-radius: 9px;
        background: #fff7ed;
        color: #9a3412;
        margin: 14px 0;
    }

    .civ-dem .success {
        background: #ecfdf5;
        color: #047857;
    }

    .civ-dem .checklist {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
        margin-top: 8px;
    }

    .civ-dem .checklist label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 400;
    }

    .civ-dem .checklist input {
        width: auto;
    }

    .civ-dem .hint {
        font-size: 12px;
        color: #64748b;
        margin-top: 5px;
    }

    .civ-dem .status {
        font-size: 13px;
    }

    @media (max-width: 650px) {
        .civ-dem .fields,
        .civ-dem .checklist {
            grid-template-columns: 1fr;
        }

        .civ-dem .panel {
            padding: 16px;
        }
    }
</style>

<div class="civ-dem">

    <!-- HEADER -->

    <h1>Gestion des démarches et documents requis</h1>

    <p class="lead">
        Créez les documents à fournir, puis associez-les
        à une démarche et à l'administration compétente.
        Les démarches actives apparaissent dans le Front Office.
    </p>

    <!-- MESSAGES -->

    <?php if ($error): ?>
        <div class="notice" role="alert">
            <?= demE($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['ok'])): ?>
        <div class="notice success">
            Modification enregistrée.
        </div>
    <?php endif; ?>


    <!-- ===================================== -->
    <!-- GESTION DES DOCUMENTS REQUIS          -->
    <!-- ===================================== -->

    <section class="panel" id="documents">

        <h2>
            <?= $editDoc
                ? 'Modifier le document'
                : 'Ajouter un document requis' ?>
        </h2>

        <form method="post">

            <input
                type="hidden"
                name="csrf"
                value="<?= demE(authToken()) ?>"
            >

            <input
                type="hidden"
                name="action"
                value="<?= $editDoc ? 'doc_edit' : 'doc_add' ?>"
            >

            <input
                type="hidden"
                name="id"
                value="<?= (int)($editDoc['idDocument'] ?? 0) ?>"
            >

            <div class="fields">

                <!-- Nom du document -->

                <label>
                    Nom du document *

                    <input
                        name="nom"
                        maxlength="180"
                        required
                        value="<?= demE($editDoc['nom'] ?? '') ?>"
                        placeholder="Carte d'identité nationale"
                    >
                </label>

                <!-- Statut -->

                <label>
                    Statut

                    <select name="actif">

                        <option
                            value="1"
                            <?= ($editDoc['actif'] ?? 1)
                                ? 'selected'
                                : '' ?>
                        >
                            Actif
                        </option>

                        <option
                            value="0"
                            <?= !($editDoc['actif'] ?? 1)
                                ? 'selected'
                                : '' ?>
                        >
                            Désactivé
                        </option>

                    </select>
                </label>

                <!-- Description -->

                <label class="wide">
                    Description

                    <textarea
                        name="description"
                        maxlength="5000"
                        rows="2"
                    ><?= demE($editDoc['description'] ?? '') ?></textarea>
                </label>

            </div>

            <button class="button" type="submit">
                <?= $editDoc
                    ? 'Enregistrer le document'
                    : 'Ajouter le document' ?>
            </button>

            <?php if ($editDoc): ?>

                <a
                    href="index.php?page=demarches#documents"
                    class="button secondary"
                >
                    Annuler
                </a>

            <?php endif; ?>

        </form>

    </section>


    <!-- ===================================== -->
    <!-- LISTE DES DOCUMENTS REQUIS            -->
    <!-- ===================================== -->

    <section class="panel">

        <h2>
            Documents requis (<?= count($docs) ?>)
        </h2>

        <div class="scroll">

            <table>

                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Description</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($docs as $doc): ?>

                        <tr>

                            <td>
                                <strong>
                                    <?= demE($doc['nom']) ?>
                                </strong>
                            </td>

                            <td>
                                <?= demE($doc['description']) ?>
                            </td>

                            <td>
                                <?= (int)$doc['actif']
                                    ? 'Actif'
                                    : 'Inactif' ?>
                            </td>

                            <td>

                                <a
                                    class="button secondary small"
                                    href="index.php?page=demarches&amp;editdoc=<?= (int)$doc['idDocument'] ?>#documents"
                                >
                                    Modifier
                                </a>

                                <form
                                    method="post"
                                    class="inline"
                                    onsubmit="return confirm('Supprimer ce document ?')"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf"
                                        value="<?= demE(authToken()) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="doc_delete"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$doc['idDocument'] ?>"
                                    >

                                    <button
                                        class="button danger small"
                                        type="submit"
                                    >
                                        Supprimer
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>
            </table>

        </div>

    </section>


    <!-- ===================================== -->
    <!-- GESTION DES DÉMARCHES                 -->
    <!-- ===================================== -->

    <section class="panel" id="demarches">

        <h2>
            <?= $editDemarche
                ? 'Modifier la démarche'
                : 'Ajouter une démarche' ?>
        </h2>

        <form method="post">

            <input
                type="hidden"
                name="csrf"
                value="<?= demE(authToken()) ?>"
            >

            <input
                type="hidden"
                name="action"
                value="<?= $editDemarche
                    ? 'dem_edit'
                    : 'dem_add' ?>"
            >

            <input
                type="hidden"
                name="id"
                value="<?= (int)($editDemarche['idDemarche'] ?? 0) ?>"
            >

            <div class="fields">

                <!-- Administration -->

                <label>
                    Administration *

                    <select name="idAdministration" required>

                        <option value="">
                            Choisir une administration
                        </option>

                        <?php foreach ($admins as $a): ?>

                            <option
                                value="<?= (int)$a['idAdministration'] ?>"
                                <?= ($editDemarche['idAdministration'] ?? 0) ==
                                    $a['idAdministration']
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= demE($a['nom']) ?>

                                <?= !(int)$a['actif']
                                    ? ' (désactivée)'
                                    : '' ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </label>

                <!-- Titre -->

                <label>
                    Titre de la démarche *

                    <input
                        name="titre"
                        maxlength="180"
                        required
                        value="<?= demE($editDemarche['titre'] ?? '') ?>"
                        placeholder="Obtenir un extrait de naissance"
                    >
                </label>

                <!-- Délai -->

                <label>
                    Délai

                    <input
                        name="delai"
                        maxlength="120"
                        value="<?= demE($editDemarche['delai'] ?? '') ?>"
                        placeholder="2 jours"
                    >
                </label>

                <!-- Coût -->

                <label>
                    Coût

                    <input
                        name="cout"
                        maxlength="120"
                        value="<?= demE($editDemarche['cout'] ?? '') ?>"
                        placeholder="Selon le tarif en vigueur"
                    >
                </label>

                <!-- Description -->

                <label class="wide">
                    Description

                    <textarea
                        name="description"
                        maxlength="5000"
                        rows="2"
                    ><?= demE($editDemarche['description'] ?? '') ?></textarea>
                </label>

                <!-- Étapes -->

                <label class="wide">
                    Étapes
                    (une par ligne au format « Titre | Explication »)

                    <textarea
                        name="etapes"
                        maxlength="5000"
                        rows="4"
                        placeholder="Préparer le dossier | Rassembler les pièces nécessaires&#10;Déposer la demande | Se présenter au guichet"
                    ><?= demE($editDemarche['etapes'] ?? '') ?></textarea>
                </label>

                <!-- Documents requis -->

                <div class="wide">

                    <strong>
                        Documents requis * (au moins un)
                    </strong>

                    <div class="checklist">

                        <?php foreach ($docs as $doc): ?>

                            <label>

                                <input
                                    type="checkbox"
                                    name="documents[]"
                                    value="<?= (int)$doc['idDocument'] ?>"
                                    <?= in_array(
                                        (int)$doc['idDocument'],
                                        $selectedDocs,
                                        true
                                    ) ? 'checked' : '' ?>
                                >

                                <?= demE($doc['nom']) ?>

                                <?= !(int)$doc['actif']
                                    ? ' (inactif)'
                                    : '' ?>

                            </label>

                        <?php endforeach; ?>

                    </div>

                    <?php if (!$docs): ?>

                        <p class="hint">
                            Ajoutez d'abord un document requis ci-dessus.
                        </p>

                    <?php endif; ?>

                </div>

                <!-- Statut -->

                <label>
                    Statut

                    <select name="actif">

                        <option
                            value="1"
                            <?= ($editDemarche['actif'] ?? 1)
                                ? 'selected'
                                : '' ?>
                        >
                            Active
                        </option>

                        <option
                            value="0"
                            <?= !($editDemarche['actif'] ?? 1)
                                ? 'selected'
                                : '' ?>
                        >
                            Désactivée
                        </option>

                    </select>
                </label>

            </div>

            <button
                class="button"
                type="submit"
                <?= !$docs ? 'disabled' : '' ?>
            >
                <?= $editDemarche
                    ? 'Enregistrer la démarche'
                    : 'Ajouter la démarche' ?>
            </button>

            <?php if ($editDemarche): ?>

                <a
                    class="button secondary"
                    href="index.php?page=demarches#demarches"
                >
                    Annuler
                </a>

            <?php endif; ?>

        </form>

    </section>


    <!-- ===================================== -->
    <!-- LISTE DES DÉMARCHES                   -->
    <!-- ===================================== -->

    <section class="panel">

        <h2>
            Démarches (<?= count($demarches) ?>)
        </h2>

        <div class="scroll">

            <table>

                <thead>
                    <tr>
                        <th>Démarche</th>
                        <th>Administration</th>
                        <th>Documents</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($demarches as $d): ?>

                        <tr>

                            <!-- Titre -->

                            <td>
                                <strong>
                                    <?= demE($d['titre']) ?>
                                </strong>
                            </td>

                            <!-- Administration -->

                            <td>
                                <?= demE($d['administrationNom']) ?>
                            </td>

                            <!-- Nombre de documents -->

                            <td>
                                <?= count($d['documentIds']) ?>
                            </td>

                            <!-- Statut -->

                            <td>
                                <?= (int)$d['actif']
                                    ? 'Active'
                                    : 'Désactivée' ?>
                            </td>

                            <!-- Actions -->

                            <td>

                                <a
                                    class="button secondary small"
                                    href="index.php?page=demarches&amp;editdem=<?= (int)$d['idDemarche'] ?>#demarches"
                                >
                                    Modifier
                                </a>

                                <form
                                    class="inline"
                                    method="post"
                                    onsubmit="return confirm('Supprimer cette démarche ?')"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf"
                                        value="<?= demE(authToken()) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="dem_delete"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$d['idDemarche'] ?>"
                                    >

                                    <button
                                        class="button danger small"
                                        type="submit"
                                    >
                                        Supprimer
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </section>

</div>
