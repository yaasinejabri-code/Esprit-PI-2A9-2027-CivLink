<?php
/**
 * Views/BackOffice/pages/profile.php
 * Attend : $profile (tableau)
 */
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 md:p-6">
  <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Mon profil</h3>

  <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
      <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Prénom</p>
      <p class="text-sm font-medium text-gray-800 dark:text-white/90"><?= $e($profile['prenom']) ?></p>
    </div>
    <div>
      <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Nom</p>
      <p class="text-sm font-medium text-gray-800 dark:text-white/90"><?= $e($profile['nom']) ?></p>
    </div>
    <div>
      <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Email</p>
      <p class="text-sm font-medium text-gray-800 dark:text-white/90"><?= $e($profile['email']) ?></p>
    </div>
    <div>
      <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Rôle</p>
      <p class="text-sm font-medium text-gray-800 dark:text-white/90"><?= $e($profile['nomRole']) ?></p>
    </div>
    <div>
      <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Membre depuis</p>
      <p class="text-sm font-medium text-gray-800 dark:text-white/90"><?= $e($profile['dateCreation']) ?></p>
    </div>
  </div>
</div>