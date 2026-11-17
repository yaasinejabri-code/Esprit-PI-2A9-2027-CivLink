<?php
$statutLabels  = ['en_attente' => 'En attente', 'en_cours' => 'En cours', 'resolu' => 'Résolu'];
$statutClasses = ['en_attente' => 'bg-warning text-dark', 'en_cours' => 'bg-info text-dark', 'resolu' => 'bg-success'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="m-0">Signalements</h2>
    <a href="index.php?page=signalements&action=create" class="btn btn-civ">+ Signaler un problème</a>
</div>

<?php if (empty($signalements)): ?>
    <div class="alert alert-info">Aucun signalement pour le moment.</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr><th>#</th><th>Titre</th><th>Catégorie</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($signalements as $s): ?>
            <?php $st = $s['statut'] ?? 'en_attente'; ?>
            <tr>
                <td><?= (int) $s['id'] ?></td>
                <td><?= htmlspecialchars($s['titre'] ?? '') ?></td>
                <td><?= htmlspecialchars($s['categorie'] ?? '') ?></td>
                <td><span class="badge <?= $statutClasses[$st] ?? 'bg-secondary' ?>"><?= htmlspecialchars($statutLabels[$st] ?? $st) ?></span></td>
                <td><?= htmlspecialchars($s['date_creation'] ?? '') ?></td>
                <td class="d-flex gap-2">
                    <?php if ($st === 'en_attente'): ?>
                        <a class="btn btn-sm btn-outline-secondary" href="index.php?page=signalements&action=edit&id=<?= (int) $s['id'] ?>">Modifier</a>
                    <?php endif; ?>
                    <form method="post" action="index.php?page=signalements&action=delete" onsubmit="return confirm('Supprimer ce signalement ?');">
                        <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>