<?php
/**
 * Views/BackOffice/pages/rendez-vous.php
 * Attend : $creneaux, $rdvs, $types, $flash, $csrf
 */
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$action = '/CivLink/Views/BackOffice/index.php?page=';

// Nom lisible d'un service à partir de sa clé
$nomService = function ($cle) use ($types) {
    return isset($types[$cle]['nom']) ? $types[$cle]['nom'] : $cle;
};

// Libellé lisible d'un statut
$libelleStatut = function ($statut) {
    if ($statut === 'confirme') return 'Confirmé';
    if ($statut === 'annule')   return 'Annulé';
    return 'En attente';
};
?>

<?php if (!empty($flash)): ?>
  <div class="mb-4 rounded-lg px-4 py-3 text-sm"
       style="<?= $flash['type'] === 'success'
         ? 'background:#ecfdf3;color:#027a48;'
         : 'background:#fef3f2;color:#b42318;' ?>">
    <?= $e($flash['text']) ?>
  </div>
<?php endif; ?>

<!-- ===== Ajouter un créneau ===== -->
<div class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 md:p-6">
  <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Ajouter un créneau horaire</h3>

  <form method="post" action="<?= $action ?>ajouter-creneau" class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-5">
    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">

    <label class="text-xs text-gray-500 dark:text-gray-400">
      Service
      <select name="typeService" class="mt-1 w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900" required>
        <?php foreach ($types as $cle => $t): ?>
          <option value="<?= $e($cle) ?>"><?= $e($t['nom']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label class="text-xs text-gray-500 dark:text-gray-400">
      Date
      <input type="date" name="dateRendezVous" class="mt-1 w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900" required>
    </label>

    <label class="text-xs text-gray-500 dark:text-gray-400">
      Heure début
      <input type="time" name="heureDebut" class="mt-1 w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900" required>
    </label>

    <label class="text-xs text-gray-500 dark:text-gray-400">
      Heure fin
      <input type="time" name="heureFin" class="mt-1 w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900" required>
    </label>

    <div class="flex items-end">
      <button type="submit" class="w-full rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
        Ajouter
      </button>
    </div>
  </form>
</div>

<!-- ===== Liste des créneaux ===== -->
<div class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 md:p-6">
  <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">
    Créneaux horaires (<?= count($creneaux) ?>)
  </h3>

  <div class="max-w-full overflow-x-auto">
    <table class="min-w-full text-left text-sm">
      <thead>
        <tr class="border-b border-gray-200 text-gray-500 dark:border-gray-800 dark:text-gray-400">
          <th class="py-3 pr-4 font-medium">Service</th>
          <th class="py-3 pr-4 font-medium">Date</th>
          <th class="py-3 pr-4 font-medium">Heure début</th>
          <th class="py-3 pr-4 font-medium">Heure fin</th>
          <th class="py-3 pr-4 font-medium">Disponibilité</th>
          <th class="py-3 font-medium">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($creneaux as $c): ?>
          <tr class="border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-300">
            <td class="py-3 pr-4"><?= $e($nomService($c['typeService'])) ?></td>
            <td class="py-3 pr-4"><?= $e($c['dateRendezVous']) ?></td>
            <td class="py-3 pr-4"><?= $e(substr($c['heureDebut'], 0, 5)) ?></td>
            <td class="py-3 pr-4"><?= $e(substr($c['heureFin'], 0, 5)) ?></td>
            <td class="py-3 pr-4"><?= $c['disponibilite'] ? 'Disponible' : 'Occupé' ?></td>
            <td class="py-3">
              <form method="post" action="<?= $action ?>supprimer-creneau">
                <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                <input type="hidden" name="idCreneau" value="<?= $e($c['idCreneau']) ?>">
                <button type="submit" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">
                  Supprimer
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($creneaux)): ?>
          <tr><td colspan="6" class="py-3 text-center text-gray-400">Aucun créneau pour le moment.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ===== Liste des rendez-vous ===== -->
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 md:p-6">
  <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">
    Rendez-vous (<?= count($rdvs) ?>)
  </h3>

  <div class="max-w-full overflow-x-auto">
    <table class="min-w-full text-left text-sm">
      <thead>
        <tr class="border-b border-gray-200 text-gray-500 dark:border-gray-800 dark:text-gray-400">
          <th class="py-3 pr-4 font-medium">Citoyen</th>
          <th class="py-3 pr-4 font-medium">Service</th>
          <th class="py-3 pr-4 font-medium">Date / Heure</th>
          <th class="py-3 pr-4 font-medium">Statut</th>
          <th class="py-3 font-medium">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rdvs as $r): ?>
          <tr class="border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-300">
            <td class="py-3 pr-4"><?= $e($r['prenom'] . ' ' . $r['nom']) ?><br><small class="text-gray-400"><?= $e($r['email']) ?></small></td>
            <td class="py-3 pr-4"><?= $e($nomService($r['typeService'])) ?></td>
            <td class="py-3 pr-4"><?= $e($r['dateRendezVous']) ?> — <?= $e(substr($r['heureDebut'], 0, 5)) ?>-<?= $e(substr($r['heureFin'], 0, 5)) ?></td>
            <td class="py-3 pr-4"><?= $e($libelleStatut($r['statut'])) ?></td>
            <td class="py-3">
              <div class="flex flex-wrap items-center gap-2">
                <?php if ($r['statut'] !== 'confirme' && $r['statut'] !== 'annule'): ?>
                  <form method="post" action="<?= $action ?>confirmer-rdv">
                    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                    <input type="hidden" name="idRdv" value="<?= $e($r['idRdv']) ?>">
                    <button type="submit" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">
                      Confirmer
                    </button>
                  </form>
                <?php endif; ?>

                <?php if ($r['statut'] !== 'annule'): ?>
                  <form method="post" action="<?= $action ?>annuler-rdv">
                    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                    <input type="hidden" name="idRdv" value="<?= $e($r['idRdv']) ?>">
                    <button type="submit" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">
                      Annuler
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($rdvs)): ?>
          <tr><td colspan="5" class="py-3 text-center text-gray-400">Aucun rendez-vous pour le moment.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
