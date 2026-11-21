<?php
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$statuts = [
    'en_attente' => ['En attente', 'bg-warning text-dark'],
    'en_cours'   => ['En cours',   'bg-info text-dark'],
    'resolu'     => ['Résolu',     'bg-success'],
];
$messages = ['1' => 'Signalement envoyé ✅', '2' => 'Signalement modifié ✅', '3' => 'Signalement supprimé ✅'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="m-0">Signalements</h2>
  <a href="<?= BASE_URL ?>/index.php?page=signalements&action=create" class="btn btn-civ">+ Signaler un problème</a>
</div>

<?php if (isset($_GET['ok'], $messages[$_GET['ok']])): ?>
  <div class="alert alert-success"><?= $messages[$_GET['ok']] ?></div>
<?php endif; ?>

<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead>
      <tr><th>#</th><th>Titre</th><th>Catégorie</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($signalements as $s): ?>
      <?php [$label, $cls] = $statuts[$s['statut']] ?? [$s['statut'], 'bg-secondary']; ?>
      <tr>
        <td><?= (int) $s['id'] ?></td>
        <td><?= $e($s['titre']) ?></td>
        <td><?= $e($s['categorie']) ?></td>
        <td><span class="badge <?= $cls ?>"><?= $e($label) ?></span></td>
        <td><?= $e($s['date_creation']) ?></td>
        <td>
          <div class="d-flex gap-1">
            <?php if ($s['statut'] === 'en_attente'): ?>
              <a class="btn btn-sm btn-outline-secondary"
                 href="<?= BASE_URL ?>/index.php?page=signalements&action=edit&id=<?= (int) $s['id'] ?>">Modifier</a>
            <?php endif; ?>
            <form method="post" action="<?= BASE_URL ?>/index.php?page=signalements&action=delete"
                  onsubmit="return confirm('Supprimer ce signalement ?');">
              <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Supprimer</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$signalements): ?>
      <tr><td colspan="6" class="text-center text-muted">Aucun signalement pour le moment.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>