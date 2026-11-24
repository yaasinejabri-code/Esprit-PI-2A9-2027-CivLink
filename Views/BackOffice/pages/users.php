<?php
/**
 * Views/BackOffice/pages/users.php
 * Attend : $users, $flash, $csrf, $currentId
 */
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$action = '/CivLink/Views/BackOffice/index.php?page=';

$inputCls = 'w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900';
$btnCls   = 'rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5';
?>

<?php if (!empty($flash)): ?>
  <div class="mb-4 rounded-lg px-4 py-3 text-sm"
       style="<?= $flash['type'] === 'success'
         ? 'background:#ecfdf3;color:#027a48;'
         : 'background:#fef3f2;color:#b42318;' ?>">
    <?= $e($flash['text']) ?>
  </div>
<?php endif; ?>

<!-- ============ AJOUTER UN UTILISATEUR ============ -->
<details class="mb-4 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 md:p-6">
  <summary class="cursor-pointer text-sm font-semibold text-gray-800 dark:text-white/90">
    + Ajouter un utilisateur
  </summary>

  <form method="post" action="<?= $action ?>users-create" id="form-create"
        class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2" novalidate>
    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">

    <div>
      <label class="mb-1 block text-xs text-gray-500" for="c-prenom">Prénom</label>
      <input class="<?= $inputCls ?>" id="c-prenom" name="prenom" type="text" required maxlength="80">
    </div>
    <div>
      <label class="mb-1 block text-xs text-gray-500" for="c-nom">Nom</label>
      <input class="<?= $inputCls ?>" id="c-nom" name="nom" type="text" required maxlength="80">
    </div>
    <div>
      <label class="mb-1 block text-xs text-gray-500" for="c-email">Email</label>
      <input class="<?= $inputCls ?>" id="c-email" name="email" type="email" required maxlength="254">
    </div>
    <div>
      <label class="mb-1 block text-xs text-gray-500" for="c-password">Mot de passe (8 à 72 caractères)</label>
      <input class="<?= $inputCls ?>" id="c-password" name="password" type="password"
             required minlength="8" maxlength="72" autocomplete="new-password">
    </div>
    <div>
      <label class="mb-1 block text-xs text-gray-500" for="c-role">Rôle</label>
      <select class="<?= $inputCls ?>" id="c-role" name="role" required>
        <option value="citoyen">citoyen</option>
        <option value="administrateur">administrateur</option>
      </select>
    </div>
    <div class="flex items-end">
      <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900">
        Créer
      </button>
    </div>
    <p id="create-error" class="text-xs md:col-span-2" style="color:#b42318;"></p>
  </form>
</details>

<!-- ============ LISTE ============ -->
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
                    <button type="submit" class="<?= $btnCls ?>">Changer</button>
                  </form>

                  <!-- Activer / désactiver -->
                  <form method="post" action="<?= $action ?>users-toggle">
                    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                    <input type="hidden" name="id" value="<?= $e($u['idUtilisateur']) ?>">
                    <button type="submit" class="<?= $btnCls ?>">
                      <?= $u['actif'] ? 'Désactiver' : 'Activer' ?>
                    </button>
                  </form>

                  <!-- Modifier (ouvre la fenêtre) -->
                  <button type="button" class="<?= $btnCls ?> js-edit"
                          data-id="<?= $e($u['idUtilisateur']) ?>"
                          data-prenom="<?= $e($u['prenom']) ?>"
                          data-nom="<?= $e($u['nom']) ?>"
                          data-email="<?= $e($u['email']) ?>">
                    Modifier
                  </button>

                  <!-- Supprimer -->
                  <form method="post" action="<?= $action ?>users-delete"
                        onsubmit="return confirm('Supprimer définitivement cet utilisateur ?');">
                    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                    <input type="hidden" name="id" value="<?= $e($u['idUtilisateur']) ?>">
                    <button type="submit" class="rounded-lg border px-3 py-1.5 text-xs font-medium hover:bg-red-50"
                            style="border-color:#fda29b;color:#b42318;">
                      Supprimer
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

<!-- ============ FENÊTRE MODIFIER ============ -->
<dialog id="dlg-edit" class="w-full max-w-md rounded-2xl p-6 backdrop:bg-black/40 dark:bg-gray-900 dark:text-white">
  <h3 class="mb-4 text-lg font-semibold">Modifier l'utilisateur</h3>
  <form method="post" action="<?= $action ?>users-update" id="form-edit" class="space-y-4" novalidate>
    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
    <input type="hidden" name="id" id="e-id">

    <div>
      <label class="mb-1 block text-xs text-gray-500" for="e-prenom">Prénom</label>
      <input class="<?= $inputCls ?>" id="e-prenom" name="prenom" type="text" required maxlength="80">
    </div>
    <div>
      <label class="mb-1 block text-xs text-gray-500" for="e-nom">Nom</label>
      <input class="<?= $inputCls ?>" id="e-nom" name="nom" type="text" required maxlength="80">
    </div>
    <div>
      <label class="mb-1 block text-xs text-gray-500" for="e-email">Email</label>
      <input class="<?= $inputCls ?>" id="e-email" name="email" type="email" required maxlength="254">
    </div>

    <p id="edit-error" class="text-xs" style="color:#b42318;"></p>

    <div class="flex justify-end gap-2">
      <button type="button" id="edit-cancel" class="<?= $btnCls ?>">Annuler</button>
      <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900">
        Enregistrer
      </button>
    </div>
  </form>
</dialog>

<script>
(function () {
  // Confort côté client uniquement : le vrai contrôle reste en PHP (UserController).
  const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  function validate(form, errBox, needPassword) {
    const v = n => (form.elements[n]?.value ?? '').trim();
    let msg = '';
    if (!v('prenom') || !v('nom'))            msg = 'Le prénom et le nom sont obligatoires.';
    else if (v('prenom').length > 80 || v('nom').length > 80) msg = 'Nom et prénom : 80 caractères maximum.';
    else if (!emailRe.test(v('email')))       msg = 'Adresse e-mail invalide.';
    else if (needPassword) {
      const p = form.elements['password'].value;
      if (p.length < 8 || p.length > 72)      msg = 'Mot de passe : 8 à 72 caractères.';
    }
    errBox.textContent = msg;
    return msg === '';
  }

  const formCreate = document.getElementById('form-create');
  formCreate.addEventListener('submit', ev => {
    if (!validate(formCreate, document.getElementById('create-error'), true)) ev.preventDefault();
  });

  const dlg = document.getElementById('dlg-edit');
  const formEdit = document.getElementById('form-edit');

  document.querySelectorAll('.js-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('e-id').value     = btn.dataset.id;
      document.getElementById('e-prenom').value = btn.dataset.prenom;
      document.getElementById('e-nom').value    = btn.dataset.nom;
      document.getElementById('e-email').value  = btn.dataset.email;
      document.getElementById('edit-error').textContent = '';
      dlg.showModal();
    });
  });

  document.getElementById('edit-cancel').addEventListener('click', () => dlg.close());

  formEdit.addEventListener('submit', ev => {
    if (!validate(formEdit, document.getElementById('edit-error'), false)) ev.preventDefault();
  });
})();
</script>