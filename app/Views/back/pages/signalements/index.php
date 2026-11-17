<?php
$labels  = ['en_attente' => 'En attente', 'en_cours' => 'En cours', 'resolu' => 'Résolu'];
$classes = ['en_attente' => 'bg-warning text-dark', 'en_cours' => 'bg-info text-dark', 'resolu' => 'bg-success'];
?>
<h2 class="mb-3">Gestion des signalements</h2>

<form method="get" action="admin.php" class="row g-2 mb-3">
    <input type="hidden" name="page" value="signalements">
    <div class="col-md-3">
        <select name="statut" class="form-select">
            <option value="">Tous les statuts</option>
            <?php foreach ($labels as $k => $l): ?>
                <option value="<?= $k ?>" <?= $filtreStatut === $k ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select name="categorie_id" class="form-select">
            <option value="0">Toutes les catégories</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= $filtreCat === (int) $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-civ w-100" type="submit">Filtrer</button></div>
    <div class="col-md-2"><a class="btn btn-outline-secondary w-100" href="admin.php?page=signalements">Réinitialiser</a></div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0 align-middle">
            <thead><tr><th>#</th><th>Titre</th><th>Catégorie</th><th>Statut</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($signalements as $s): ?>
                <?php $st = $s['statut'] ?? 'en_attente'; ?>
                <tr>
                    <td><?= (int) $s['id'] ?></td>
                    <td><?= htmlspecialchars($s['titre'] ?? '') ?></td>
                    <td><?= htmlspecialchars($s['categorie'] ?? '') ?></td>
                    <td><span class="badge <?= $classes[$st] ?? 'bg-secondary' ?>"><?= htmlspecialchars($labels[$st] ?? $st) ?></span></td>
                    <td><?= htmlspecialchars($s['date_creation'] ?? '') ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <form method="post" action="admin.php?page=signalements&action=updateStatut" class="d-flex gap-1">
                                <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                                <select name="statut" class="form-select form-select-sm">
                                    <?php foreach ($labels as $k => $l): ?>
                                        <option value="<?= $k ?>" <?= $st === $k ? 'selected' : '' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-civ" type="submit">OK</button>
                            </form>
                            <form method="post" action="admin.php?page=signalements&action=delete" onsubmit="return confirm('Supprimer ce signalement ?');">
                                <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($signalements)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Aucun signalement.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>