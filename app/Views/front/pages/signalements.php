<section class="container py-5">
    <h2>Signalements</h2>
    <?php foreach ($signalements as $s): ?>
        <div class="card mb-3 p-3">
            <h5><?= e($s['titre']) ?></h5>
            <small><?= e($s['categorie']) ?> — <?= e($s['statut']) ?></small>
        </div>
    <?php endforeach; ?>
</section>
