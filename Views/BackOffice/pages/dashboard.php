<?php
/**
 * Views/BackOffice/pages/dashboard.php
 * Contenu propre à la page (sans head, sidebar, header, footer).
 * Attend : $total, $totalAdmins, $totalCitoyens, $latest
 */
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<div class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6">
  <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 md:p-6">
    <span class="text-sm text-gray-500 dark:text-gray-400">Comptes au total</span>
    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90"><?= $e($total) ?></h4>
  </div>
  <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 md:p-6">
    <span class="text-sm text-gray-500 dark:text-gray-400">Citoyens</span>
    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90"><?= $e($totalCitoyens) ?></h4>
  </div>
  <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 md:p-6">
    <span class="text-sm text-gray-500 dark:text-gray-400">Administrateurs</span>
    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90"><?= $e($totalAdmins) ?></h4>
  </div>
</div>

<div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 md:p-6">
  <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Dernières inscriptions</h3>
  <div class="max-w-full overflow-x-auto">
    <table class="min-w-full text-left text-sm">
      <thead>
        <tr class="border-b border-gray-200 text-gray-500 dark:border-gray-800 dark:text-gray-400">
          <th class="py-3 pr-4 font-medium">ID</th>
          <th class="py-3 pr-4 font-medium">Nom</th>
          <th class="py-3 pr-4 font-medium">Email</th>
          <th class="py-3 pr-4 font-medium">Rôle</th>
          <th class="py-3 font-medium">Inscrit le</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($latest as $u): ?>
          <tr class="border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-300">
            <td class="py-3 pr-4"><?= $e($u['idUtilisateur']) ?></td>
            <td class="py-3 pr-4"><?= $e($u['prenom'] . ' ' . $u['nom']) ?></td>
            <td class="py-3 pr-4"><?= $e($u['email']) ?></td>
            <td class="py-3 pr-4"><?= $e($u['nomRole']) ?></td>
            <td class="py-3"><?= $e($u['dateCreation']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>