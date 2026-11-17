<h2 class="mb-4">Tableau de bord</h2>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card stat-card"><div class="card-body"><div class="text-muted">Total signalements</div><div class="fs-2 fw-bold"><?= (int) $total ?></div></div></div></div>
    <div class="col-md-3"><div class="card stat-card"><div class="card-body"><div class="text-muted">En attente</div><div class="fs-2 fw-bold"><?= (int) $counts['en_attente'] ?></div></div></div></div>
    <div class="col-md-3"><div class="card stat-card"><div class="card-body"><div class="text-muted">En cours</div><div class="fs-2 fw-bold"><?= (int) $counts['en_cours'] ?></div></div></div></div>
    <div class="col-md-3"><div class="card stat-card"><div class="card-body"><div class="text-muted">Résolus</div><div class="fs-2 fw-bold"><?= (int) $counts['resolu'] ?></div></div></div></div>
</div>

<div class="card">
    <div class="card-header bg-white fw-semibold">Derniers signalements</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr><th>Titre</th><th>Catégorie</th><th>Statut</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($derniers as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['titre'] ?? '') ?></td>
                    <td><?= htmlspecialchars($s['categorie'] ?? '') ?></td>
                    <td><?= htmlspecialchars($s['statut'] ?? '') ?></td>
                    <td><?= htmlspecialchars($s['date_creation'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($derniers)): ?>
                <tr><td colspan="4" class="text-center text-muted">Aucun signalement.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white"><a href="admin.php?page=signalements">Voir tous les signalements &rarr;</a></div>
</div>