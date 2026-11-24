<?php
/**
 * Views/BackOffice/partials/header.php
 * Barre du haut. Identique sur toutes les pages : aucune logique de page ici.
 * Elle lit seulement la session pour afficher l'utilisateur connecté.
 */
$assets = $assets ?? '/CivLink/Views/BackOffice/assets/';
$sessionUser = $_SESSION['user'] ?? [];
$fullName = trim(($sessionUser['prenom'] ?? '') . ' ' . ($sessionUser['nom'] ?? ''));
$initial  = strtoupper(mb_substr($sessionUser['prenom'] ?? 'A', 0, 1));
?>
<!-- App Header -->
<header
  x-data="{ isApplicationMenuOpen: false }"
  class="sticky top-0 z-50 flex w-full shrink-0 border-gray-200 bg-white xl:border-b dark:border-gray-800 dark:bg-gray-900"
>
  <div class="flex grow flex-col items-center justify-between xl:flex-row xl:px-6">

    <!-- Partie gauche -->
    <div class="flex w-full items-center justify-between gap-2 border-b border-gray-200 px-3 py-3 sm:gap-4 xl:justify-normal xl:border-b-0 xl:px-0 xl:py-4 dark:border-gray-800">

      <!-- Bouton ouvrir/fermer la sidebar -->
      <button
        type="button"
        @click.stop="sidebarToggle = !sidebarToggle"
        aria-label="Afficher ou masquer le menu"
        class="group/button relative inline-flex size-10 shrink-0 items-center justify-center rounded-[10px] border border-gray-300 bg-transparent text-gray-500 shadow-theme-xs transition-all select-none hover:bg-gray-50 hover:text-gray-800 lg:size-11 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700/50 dark:hover:text-white"
      >
        <svg x-show="sidebarToggle && window.innerWidth < 1280" class="size-6" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 6L6 18M6 6l12 12" />
        </svg>
        <svg x-show="!sidebarToggle || window.innerWidth >= 1280" width="16" height="12" viewBox="0 0 16 12" fill="none">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z" fill="currentColor" />
        </svg>
      </button>

      <!-- Logo (mobile) -->
      <a href="/CivLink/Views/BackOffice/index.php?page=dashboard" class="xl:hidden">
        <img class="h-8 w-38.5 dark:hidden" src="<?= $assets ?>images/logo/logo.svg" alt="Logo" />
        <img class="hidden h-8 w-38.5 dark:block" src="<?= $assets ?>images/logo/logo-dark.svg" alt="Logo" />
      </a>

      <!-- Bouton menu (mobile) -->
      <button
        type="button"
        @click="isApplicationMenuOpen = !isApplicationMenuOpen"
        aria-label="Afficher le menu utilisateur"
        class="inline-flex size-10 shrink-0 items-center justify-center rounded-[10px] border-none bg-transparent text-gray-700 transition-all select-none hover:bg-gray-50 xl:hidden dark:text-gray-400 dark:hover:bg-gray-700/50"
      >
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="1" /><circle cx="19" cy="12" r="1" /><circle cx="5" cy="12" r="1" />
        </svg>
      </button>

      <!-- Titre de l'espace (desktop) -->
      <div class="hidden xl:block">
        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">CivLink · Espace administrateur</span>
      </div>
    </div>

    <!-- Partie droite -->
    <div
      class="w-full items-center justify-between gap-4 px-5 py-4 shadow-theme-md xl:flex xl:justify-end xl:px-0 xl:shadow-none"
      :class="isApplicationMenuOpen ? 'flex' : 'hidden'"
    >
      <!-- Mode sombre -->
      <button
        type="button"
        @click.prevent="darkMode = !darkMode"
        aria-label="Changer le thème"
        class="relative flex size-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
      >
        <svg class="hidden dark:block" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
        </svg>
        <svg class="dark:hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>
        </svg>
      </button>

      <!-- Utilisateur connecté -->
      <div class="flex items-center gap-3">
        <span class="flex size-11 items-center justify-center rounded-full bg-brand-50 text-sm font-semibold text-brand-500 dark:bg-brand-500/[0.12] dark:text-brand-400">
          <?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?>
        </span>
        <span class="block">
          <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            <?= htmlspecialchars($fullName !== '' ? $fullName : 'Administrateur', ENT_QUOTES, 'UTF-8') ?>
          </span>
          <span class="block text-xs text-gray-500 dark:text-gray-400">
            <?= htmlspecialchars($sessionUser['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
          </span>
        </span>
      </div>
    </div>
  </div>
</header>