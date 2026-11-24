<?php
/**
 * Views/BackOffice/pages/users.php
 * Attend : $users, $flash, $csrf, $currentId
 */
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$action = '/CivLink/Views/BackOffice/index.php?page=';
?>

<?php if (!empty($flash)): ?>
  <div class="mb-4 rounded-lg px-4 py-3 text-sm"
       style="<?= $flash['type'] === 'success'
         ? 'background:#ecfdf3;color:#027a48;'
         : 'background:#fef3f2;color:#b42318;' ?>">
    <?= $e($flash['text']) ?>
  </div>
<?php endif; ?>

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 md:p-6">
  <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">
    Utilisateurs (<?= count($users) ?>)
  </h3>

  <div class="max-w-full overflow-x-auto">
    <table class="min-w-full text-left text-sm">
      <thead>
        <tr class="border-b border-gray-200 text-gray-500 dark:border-gray-800 dark:text-gray-400">
          <th class="py-3 pr-4 font-medium">ID</th>
          <th class="py-3 pr-4 font-medium">Nom</th>
          <th class="py-3 pr-4 font-medium">Email</th>
          <th class="py-3 pr-4 font-medium">Rôle</th>
          <th class="py-3 pr-4 font-medium">Statut</th>
          <th class="py-3 font-medium">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <?php $isMe = ((int) $u['idUtilisateur'] === (int) $currentId); ?>
          <tr class="border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-300">
            <td class="py-3 pr-4"><?= $e($u['idUtilisateur']) ?></td>
            <td class="py-3 pr-4"><?= $e($u['prenom'] . ' ' . $u['nom']) ?><?= $isMe ? ' (vous)' : '' ?></td>
            <td class="py-3 pr-4"><?= $e($u['email']) ?></td>
            <td class="py-3 pr-4"><?= $e($u['nomRole']) ?></td>
            <td class="py-3 pr-4"><?= $u['actif'] ? 'Actif' : 'Désactivé' ?></td>
            <td class="py-3">
              <?php if ($isMe): ?>
                <span class="text-xs text-gray-400">—</span>
              <?php else: ?>
                <div class="flex flex-wrap items-center gap-2">

                  <!-- Changer le rôle -->
                  <form method="post" action="<?= $action ?>users-role" class="flex items-center gap-2">
                    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                    <input type="hidden" name="id" value="<?= $e($u['idUtilisateur']) ?>">
                    <select name="role" class="rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-900">
                      <option value="citoyen" <?= $u['nomRole'] === 'citoyen' ? 'selected' : '' ?>>citoyen</option>
                      <option value="administrateur" <?= $u['nomRole'] === 'administrateur' ? 'selected' : '' ?>>administrateur</option>
                    </select>
                    <button type="submit" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">
                      Changer
                    </button>
                  </form>

                  <!-- Activer / désactiver -->
                  <form method="post" action="<?= $action ?>users-toggle">
                    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                    <input type="hidden" name="id" value="<?= $e($u['idUtilisateur']) ?>">
                    <button type="submit" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">
                      <?= $u['actif'] ? 'Désactiver' : 'Activer' ?>
                    </button>
                  </form>

                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>