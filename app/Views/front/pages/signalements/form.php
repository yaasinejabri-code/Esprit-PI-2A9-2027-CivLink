<?php $isEdit = !empty($signalement['id']); ?>
<h2 class="mb-3"><?= $isEdit ? 'Modifier le signalement' : 'Signaler un problème' ?></h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="index.php?page=signalements&action=<?= $isEdit ? 'update&id=' . (int) $signalement['id'] : 'store' ?>">
    <div class="mb-3">
        <label class="form-label">Titre</label>
        <input type="text" name="titre" class="form-control" value="<?= htmlspecialchars($signalement['titre'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" rows="4" class="form-control"><?= htmlspecialchars($signalement['description'] ?? '') ?></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Catégorie</label>
        <select name="categorie_id" class="form-select">
            <option value="">-- Choisir --</option>
            <?php foreach (($categories ?? []) as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (($signalement['categorie_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nom'] ?? $c['libelle'] ?? '') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Latitude</label>
            <input type="text" name="latitude" class="form-control" value="<?= htmlspecialchars($signalement['latitude'] ?? '') ?>">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Longitude</label>
            <input type="text" name="longitude" class="form-control" value="<?= htmlspecialchars($signalement['longitude'] ?? '') ?>">
        </div>
    </div>
    <button type="submit" class="btn btn-civ">Enregistrer</button>
    <a href="index.php?page=signalements" class="btn btn-outline-secondary">Annuler</a>
</form>