
<?php
if (!defined("CIVLINK_BACKOFFICE_RENDER")) {
    http_response_code(403);
    exit("Accès direct interdit.");
}
?>

<div class="act-admin">
    <div class="container-xl">

        <!-- HEADER -->
        <div class="act-admin-top">
            <div>
                <div class="act-admin-kicker">BACK OFFICE · CIVLINK</div>

                <h1>Gestion des actualités</h1>

                <p>
                    CRUD des articles et des catégories.
                    Seules les actualités publiées sont visibles
                    dans le Front Office.
                </p>
            </div>

            <a
                class="aa-btn aa-muted"
                href="/CivLink/Views/FrontOffice/pages/actualites.php"
            >
                <i class="fas fa-external-link-alt"></i>
                Voir le Front Office
            </a>
        </div>

        <?php if (false): ?>
            <div class="aa-alert">
                Accès réservé aux administrateurs.

                <a href="<?= eact($base) ?>/pages/authentification.php">
                    Se connecter
                </a>
            </div>
        <?php else: ?>

            <!-- MESSAGES -->
            <?php if ($err): ?>
                <div class="aa-alert" role="alert">
                    <?= eact($err) ?>
                </div>
            <?php endif; ?>

            <?php if ($ok): ?>
                <div class="aa-success" role="status">
                    <?= eact($ok) ?>
                </div>
            <?php endif; ?>

            <div class="aa-columns">

                <!-- FORMULAIRE : ACTUALITÉS -->
                <section class="aa-panel">
                    <h2>
                        <?= $edit
                            ? 'Modifier une actualité'
                            : 'Ajouter une actualité' ?>
                    </h2>

                    <form method="post" class="aa-form">

                        <input
                            type="hidden"
                            name="csrf"
                            value="<?= eact(authToken()) ?>"
                        >

                        <input
                            type="hidden"
                            name="action"
                            value="<?= $edit ? 'edit_article' : 'add_article' ?>"
                        >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int)($edit['idActualite'] ?? 0) ?>"
                        >

                        <label>
                            Titre *

                            <input
                                name="titre"
                                required
                                maxlength="180"
                                value="<?= eact($edit['titre'] ?? '') ?>"
                                placeholder="Titre de l'article"
                            >
                        </label>

                        <label>
                            Résumé *

                            <textarea
                                name="resume"
                                required
                                maxlength="500"
                                rows="2"
                            ><?= eact($edit['resume'] ?? '') ?></textarea>
                        </label>

                        <label>
                            Contenu *
                            (séparer les paragraphes par une ligne vide)

                            <textarea
                                name="contenu"
                                required
                                minlength="25"
                                maxlength="15000"
                                rows="9"
                            ><?= eact($edit['contenu'] ?? '') ?></textarea>
                        </label>

                        <div class="aa-pair">

                            <label>
                                Catégorie *

                                <select name="categorie" required>
                                    <option value="">Choisir...</option>

                                    <?php foreach ($categories as $c): ?>
                                        <option
                                            value="<?= (int)$c['idCategorie'] ?>"
                                            <?= (int)($edit['idCategorie'] ?? 0) ===
                                                (int)$c['idCategorie']
                                                ? 'selected'
                                                : '' ?>
                                        >
                                            <?= eact($c['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </label>

                            <label>
                                Statut *

                                <select name="statut">
                                    <option
                                        value="brouillon"
                                        <?= ($edit['statut'] ?? '') === 'brouillon'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Brouillon
                                    </option>

                                    <option
                                        value="publie"
                                        <?= ($edit['statut'] ?? '') === 'publie'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Publié
                                    </option>
                                </select>
                            </label>

                        </div>

                        <div class="aa-pair">

                            <label>
                                Icône FontAwesome

                                <input
                                    name="icone"
                                    maxlength="90"
                                    value="<?= eact($edit['icone'] ?? 'far fa-newspaper') ?>"
                                    placeholder="fas fa-newspaper"
                                >
                            </label>

                            <label>
                                Temps de lecture

                                <input
                                    name="lecture"
                                    maxlength="20"
                                    value="<?= eact($edit['lecture'] ?? '2 min') ?>"
                                >
                            </label>

                        </div>

                        <div class="aa-pair">

                            <label>
                                Lien interne (facultatif)

                                <select name="lien">
                                    <option value="">Aucun</option>

                                    <?php
                                    $liens = [
                                        'administrations.php' => 'Administrations',
                                        'demarches.php'       => 'Démarches',
                                        'signalements.php'    => 'Signalements',
                                        'factures.php'        => 'Factures',
                                        'rendezvous.php'      => 'Rendez-vous',
                                        'actualites.php'      => 'Actualités',
                                        '../index.php'       => 'Accueil'
                                    ];
                                    ?>

                                    <?php foreach ($liens as $url => $label): ?>
                                        <option
                                            value="<?= eact($url) ?>"
                                            <?= ($edit['lien'] ?? '') === $url
                                                ? 'selected'
                                                : '' ?>
                                        >
                                            <?= eact($label) ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </label>

                            <label>
                                Texte du lien

                                <input
                                    name="lienTexte"
                                    maxlength="100"
                                    value="<?= eact($edit['lienTexte'] ?? '') ?>"
                                    placeholder="Découvrir ce service"
                                >
                            </label>

                        </div>

                        <div class="aa-actions">

                            <button
                                type="submit"
                                class="aa-btn aa-primary"
                                <?= !$categories ? 'disabled' : '' ?>
                            >
                                <i class="fas fa-save"></i>

                                <?= $edit
                                    ? 'Enregistrer les modifications'
                                    : 'Ajouter l’actualité' ?>
                            </button>

                            <?php if ($edit): ?>
                                <a
                                    href="index.php?page=actualites"
                                    class="aa-btn aa-muted"
                                >
                                    Annuler
                                </a>
                            <?php endif; ?>

                        </div>

                    </form>
                </section>

                <!-- FORMULAIRE : CATÉGORIES -->
                <section class="aa-panel">

                    <h2>Gestion des catégories</h2>

                    <form method="post" class="aa-form">

                        <input
                            type="hidden"
                            name="csrf"
                            value="<?= eact(authToken()) ?>"
                        >

                        <input
                            type="hidden"
                            name="action"
                            value="<?= isset($editCategory)
                                ? 'edit_category'
                                : 'add_category' ?>"
                        >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int)($editCategory['idCategorie'] ?? 0) ?>"
                        >

                        <label>
                            Nom de la catégorie *

                            <input
                                name="nom"
                                required
                                maxlength="90"
                                value="<?= eact($editCategory['nom'] ?? '') ?>"
                            >
                        </label>

                        <label>
                            Description

                            <textarea
                                name="description"
                                rows="2"
                                maxlength="500"
                            ><?= eact($editCategory['description'] ?? '') ?></textarea>
                        </label>

                        <div class="aa-actions">

                            <button
                                class="aa-btn aa-primary"
                                type="submit"
                            >
                                <?= isset($editCategory)
                                    ? 'Modifier la catégorie'
                                    : 'Ajouter la catégorie' ?>
                            </button>

                            <?php if (isset($editCategory)): ?>
                                <a
                                    class="aa-btn aa-muted"
                                    href="index.php?page=actualites"
                                >
                                    Annuler
                                </a>
                            <?php endif; ?>

                        </div>

                    </form>

                    <!-- LISTE DES CATÉGORIES -->
                    <div class="aa-category-list">

                        <?php foreach ($categories as $c): ?>

                            <div class="aa-category-item">

                                <div>
                                    <strong>
                                        <?= eact($c['nom']) ?>
                                    </strong>

                                    <small>
                                        <?= eact($c['description']) ?>
                                    </small>
                                </div>

                                <div class="aa-actions">

                                    <a
                                        class="aa-btn aa-muted"
                                        href="index.php?page=actualites&amp;editcat=<?= (int)$c['idCategorie'] ?>"
                                    >
                                        Modifier
                                    </a>

                                    <form
                                        method="post"
                                        onsubmit="return confirm('Supprimer cette catégorie ? Impossible si des articles y sont associés.')"
                                    >

                                        <input
                                            type="hidden"
                                            name="csrf"
                                            value="<?= eact(authToken()) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete_category"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$c['idCategorie'] ?>"
                                        >

                                        <button
                                            class="aa-btn aa-danger"
                                            type="submit"
                                        >
                                            Supprimer
                                        </button>

                                    </form>

                                </div>
                            </div>

                        <?php endforeach; ?>

                    </div>

                </section>

            </div>

            <!-- TABLEAU DES ACTUALITÉS -->
            <section class="aa-panel aa-table-panel">

                <div class="aa-table-heading">
                    <h2>
                        Toutes les actualités
                        (<?= count($articles) ?>)
                    </h2>

                    <span>Brouillons et articles publiés</span>
                </div>

                <div class="aa-scroll">

                    <table>
                        <thead>
                            <tr>
                                <th>Actualité</th>
                                <th>Catégorie</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($articles as $a): ?>

                                <tr>

                                    <td>
                                        <strong>
                                            <?= eact($a['titre']) ?>
                                        </strong>

                                        <small>
                                            <?= eact($a['resume']) ?>
                                        </small>
                                    </td>

                                    <td>
                                        <?= eact($a['categorie']) ?>
                                    </td>

                                    <td>
                                        <span class="aa-status <?= $a['statut'] === 'publie'
                                            ? 'is-live'
                                            : '' ?>">
                                            <?= $a['statut'] === 'publie'
                                                ? 'Publié'
                                                : 'Brouillon' ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= eact($a['datePublication']) ?>
                                    </td>

                                    <td>
                                        <div class="aa-actions">

                                            <a
                                                class="aa-btn aa-muted"
                                                href="index.php?page=actualites&amp;edit=<?= (int)$a['idActualite'] ?>"
                                            >
                                                Modifier
                                            </a>

                                            <form
                                                method="post"
                                                onsubmit="return confirm('Supprimer définitivement cette actualité ?')"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="csrf"
                                                    value="<?= eact(authToken()) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="delete_article"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int)$a['idActualite'] ?>"
                                                >

                                                <button
                                                    class="aa-btn aa-danger"
                                                    type="submit"
                                                >
                                                    Supprimer
                                                </button>

                                            </form>
                                        </div>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                            <?php if (!$articles): ?>
                                <tr>
                                    <td colspan="5">
                                        Aucune actualité enregistrée.
                                    </td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>

                </div>

            </section>

        <?php endif; ?>

    </div>
</div>
