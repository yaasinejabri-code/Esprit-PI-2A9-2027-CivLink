<?php
if (!defined('CIVLINK_BACKOFFICE_RENDER')) {
    http_response_code(403);
    exit;
}
if (!function_exists('admE')) {
    function admE($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}
$csrf = admE($csrfToken);
$editActif = $edit ? $edit->isActif() : true;
$editServiceActif = $editService ? $editService->isActif() : true;
?>
<link rel="stylesheet" href="/CivLink/Views/BackOffice/assets/administrations_admin.css">
<div class="civ-adm">
    <h1>Gestion des administrations et services</h1>
    <p class="desc">Ajoutez et gérez les lieux qui apparaissent sur la carte du Front Office et les services rattachés à chaque administration.</p>
    <?php if ($error): ?>
        <div class="notice" role="alert"><?= admE($error) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['ok'])): ?>
        <div class="notice good">Modification enregistrée.</div>
    <?php endif; ?>

    <section class="panel">
        <h2><?= $edit ? 'Modifier une administration' : 'Ajouter une administration' ?></h2>
        <form method="post" id="admForm" novalidate>
            <input type="hidden" name="csrf" value="<?= $csrf ?>">
            <input type="hidden" name="action" value="<?= $edit ? 'admin_edit' : 'admin_add' ?>">
            <input type="hidden" name="id" value="<?= $edit ? $edit->getIdAdministration() : 0 ?>">
            <div class="grid">
                <label>Nom *
                    <input name="nom" required maxlength="160" value="<?= admE($edit ? $edit->getNom() : '') ?>" placeholder="Municipalité de Tunis">
                </label>
                <label>Type *
                    <select name="typeAdministration" required>
                        <?php foreach ($types as $key => $t): ?>
                            <option value="<?= admE($key) ?>" <?= $edit && $edit->getTypeAdministration() === $key ? 'selected' : '' ?>><?= admE($t['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="location">
                    <label>Adresse *
                        <input id="admAdresse" name="adresse" required maxlength="220" autocomplete="off" value="<?= admE($edit ? $edit->getAdresse() : '') ?>" placeholder="Recherchez une adresse en Tunisie">
                    </label>
                    <div id="admSuggestions"></div>
                </div>
                <label>Horaires
                    <input name="horaires" maxlength="120" value="<?= admE($edit ? $edit->getHoraires() : '') ?>" placeholder="Lun–Ven, 8h–16h">
                </label>
                <label>Téléphone
                    <input name="telephone" maxlength="40" value="<?= admE($edit ? $edit->getTelephone() : '') ?>" placeholder="+216 ...">
                </label>
                <label>Latitude *
                    <input type="number" step="any" min="-90" max="90" id="admLat" name="latitude" required value="<?= $edit ? admE($edit->getLatitude()) : '' ?>" placeholder="36.8065">
                </label>
                <label>Longitude *
                    <input type="number" step="any" min="-180" max="180" id="admLng" name="longitude" required value="<?= $edit ? admE($edit->getLongitude()) : '' ?>" placeholder="10.1815">
                </label>
                <label>Statut
                    <select name="actif">
                        <option value="1" <?= $editActif ? 'selected' : '' ?>>Actif</option>
                        <option value="0" <?= !$editActif ? 'selected' : '' ?>>Désactivé</option>
                    </select>
                </label>
            </div>
            <button class="button" type="submit"><?= $edit ? 'Enregistrer les modifications' : 'Ajouter l’administration' ?></button>
            <?php if ($edit): ?>
                <a class="button secondary" href="index.php?page=administrations">Annuler</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="panel">
        <h2>Administrations (<?= count($admins) ?>)</h2>
        <div class="scroll">
            <table>
                <thead>
                    <tr><th>Administration</th><th>Type</th><th>Adresse</th><th>État</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $a): ?>
                        <tr>
                            <td><strong><?= admE($a->getNom()) ?></strong></td>
                            <td><?= admE($types[$a->getTypeAdministration()]['nom'] ?? $a->getTypeAdministration()) ?></td>
                            <td><?= admE($a->getAdresse()) ?></td>
                            <td><?= $a->isActif() ? 'Active' : 'Désactivée' ?></td>
                            <td>
                                <a class="button secondary tiny" href="index.php?page=administrations&amp;edit=<?= $a->getIdAdministration() ?>">Modifier</a>
                                <form class="inline" method="post" data-confirm="Supprimer cette administration ? (impossible si des services ou des démarches y sont rattachés)">
                                    <input type="hidden" name="csrf" value="<?= $csrf ?>">
                                    <input type="hidden" name="action" value="admin_delete">
                                    <input type="hidden" name="id" value="<?= $a->getIdAdministration() ?>">
                                    <button class="button danger tiny" type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel" id="services">
        <h2><?= $editService ? 'Modifier un service' : 'Ajouter un service' ?></h2>
        <form method="post" id="svcForm" novalidate>
            <input type="hidden" name="csrf" value="<?= $csrf ?>">
            <input type="hidden" name="action" value="<?= $editService ? 'service_edit' : 'service_add' ?>">
            <input type="hidden" name="id" value="<?= $editService ? $editService->getIdService() : 0 ?>">
            <div class="grid">
                <label>Administration *
                    <select name="idAdministration" required>
                        <option value="">Choisir une administration</option>
                        <?php foreach ($admins as $a): ?>
                            <option value="<?= $a->getIdAdministration() ?>" <?= $editService && $editService->getIdAdministration() === $a->getIdAdministration() ? 'selected' : '' ?>><?= admE($a->getNom()) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Nom du service *
                    <input name="nomService" required maxlength="120" value="<?= admE($editService ? $editService->getNom() : '') ?>" placeholder="État civil">
                </label>
                <label class="full">Description
                    <textarea name="description" maxlength="2000" rows="2"><?= admE($editService ? $editService->getDescription() : '') ?></textarea>
                </label>
                <label>Statut
                    <select name="actif">
                        <option value="1" <?= $editServiceActif ? 'selected' : '' ?>>Actif</option>
                        <option value="0" <?= !$editServiceActif ? 'selected' : '' ?>>Désactivé</option>
                    </select>
                </label>
            </div>
            <button class="button" type="submit"><?= $editService ? 'Enregistrer les modifications' : 'Ajouter le service' ?></button>
            <?php if ($editService): ?>
                <a class="button secondary" href="index.php?page=administrations#services">Annuler</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="panel">
        <h2>Services (<?= count($services) ?>)</h2>
        <div class="scroll">
            <table>
                <thead>
                    <tr><th>Service</th><th>Administration</th><th>Description</th><th>État</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $s): ?>
                        <tr>
                            <td><strong><?= admE($s->getNom()) ?></strong></td>
                            <td><?= admE($adminNames[$s->getIdAdministration()] ?? '') ?></td>
                            <td><?= admE($s->getDescription()) ?></td>
                            <td><?= $s->isActif() ? 'Actif' : 'Désactivé' ?></td>
                            <td>
                                <a class="button secondary tiny" href="index.php?page=administrations&amp;editservice=<?= $s->getIdService() ?>#services">Modifier</a>
                                <form class="inline" method="post" data-confirm="Supprimer ce service ?">
                                    <input type="hidden" name="csrf" value="<?= $csrf ?>">
                                    <input type="hidden" name="action" value="service_delete">
                                    <input type="hidden" name="id" value="<?= $s->getIdService() ?>">
                                    <button class="button danger tiny" type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<script src="/CivLink/Views/BackOffice/assets/administrations_admin.js"></script>
