<section class="p-4">
    <h2>Gestion des signalements</h2>
    <table class="w-full">
        <tr><th>Titre</th><th>Catégorie</th><th>Statut</th></tr>
        <?php foreach ($signalements as $s): ?>
        <tr><td><?= e($s['titre']) ?></td><td><?= e($s['categorie']) ?></td><td><?= e($s['statut']) ?></td></tr>
        <?php endforeach; ?>
    </table>
</section>
