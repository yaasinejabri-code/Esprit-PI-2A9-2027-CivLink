<?php
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$isEdit = ($mode ?? 'create') === 'edit';
$action = 'index.php?page=signalements&action=' . ($isEdit ? 'edit&id=' . (int) $id : 'create');
?>
<div style="max-width: 720px;">
  <h2><?= $isEdit ? 'Modifier le signalement' : 'Signaler un problème' ?></h2>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= $e($err) ?></div>
  <?php endforeach; ?>

  <form method="post" action="<?= $action ?>" enctype="multipart/form-data" novalidate>
    <div class="mb-3">
      <label class="form-label">Titre</label>
      <input type="text" name="titre" class="form-control" value="<?= $e($old['titre']) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Catégorie</label>
      <select name="categorie_id" class="form-select">
        <option value="">-- Choisir --</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int) $c['id'] ?>" <?= (int) $old['categorie_id'] === (int) $c['id'] ? 'selected' : '' ?>>
            <?= $e($c['nom']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" rows="4" class="form-control"><?= $e($old['description']) ?></textarea>
    </div>

    <div class="row mb-3">
      <div class="col">
        <label class="form-label">Latitude (optionnel)</label>
        <input type="text" name="latitude" class="form-control" placeholder="36.8065" value="<?= $e($old['latitude']) ?>">
      </div>
      <div class="col">
        <label class="form-label">Longitude (optionnel)</label>
        <input type="text" name="longitude" class="form-control" placeholder="10.1815" value="<?= $e($old['longitude']) ?>">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Photo (optionnel, 2 Mo max)</label>
      <?php if ($isEdit && !empty($old['photo'])): ?>
        <div class="mb-2"><img src="uploads/signalements/<?= $e($old['photo']) ?>" height="80" alt=""></div>
      <?php endif; ?>
      <input type="file" name="photo" class="form-control" accept="image/*">
    </div>

    <button type="submit" class="btn btn-civ"><?= $isEdit ? 'Enregistrer' : 'Envoyer le signalement' ?></button>
    <a href="index.php?page=signalements" class="btn btn-outline-secondary">Annuler</a>
  </form>
</div>