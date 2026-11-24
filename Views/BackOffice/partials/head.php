<?php
/**
 * Views/BackOffice/partials/head.php
 * Attend : $title (string), $currentPage (string)
 */
$assets = '/CivLink/Views/BackOffice/assets/';
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title><?= htmlspecialchars($title ?? 'Back Office', ENT_QUOTES, 'UTF-8') ?> | CivLink Admin</title>
    <link rel="icon" href="<?= $assets ?>favicon.ico" />
    <!-- Applique le thème tout de suite (évite le flash blanc au chargement) -->
    <script>
      try {
        document.documentElement.classList.toggle('dark', JSON.parse(localStorage.getItem('darkMode')) === true);
      } catch (e) {}
    </script>
    <link href="<?= $assets ?>style.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <?php if (($currentPage ?? '') === 'actualites'): ?>
    <link rel="stylesheet" href="<?= $assets ?>actualites_admin.css" />
    <?php elseif (($currentPage ?? '') === 'rendezvous'): ?>
    <link rel="stylesheet" href="<?= $assets ?>rendezvous.css" />
    <?php endif; ?>
    <script defer src="<?= $assets ?>bundle.js"></script>
  </head>
  <body
    x-data="{ page: '<?= htmlspecialchars($currentPage ?? 'dashboard', ENT_QUOTES, 'UTF-8') ?>', 'loaded': true, 'darkMode': false, 'stickyMenu': false, 'sidebarToggle': false, 'scrollTop': false }"
    x-init="
         darkMode = JSON.parse(localStorage.getItem('darkMode')) ?? false;
         document.documentElement.classList.toggle('dark', darkMode);
         $watch('darkMode', value => {
           localStorage.setItem('darkMode', JSON.stringify(value));
           document.documentElement.classList.toggle('dark', value);
         })"
    :class="{'dark bg-gray-900': darkMode === true}"
  >
    <!-- ===== Preloader Start ===== -->
    <div
      x-show="loaded"
      x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})"
      class="fixed top-0 left-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black"
    >
      <div
        class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"
      ></div>
    </div>
    <!-- ===== Preloader End ===== -->