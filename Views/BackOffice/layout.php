<?php
/**
 * Views/BackOffice/layout.php
 * Assemble toutes les parties communes du back-office.
 * Attend : $title (string), $currentPage (string), $content (string, HTML de la page)
 * Optionnel : $extraScripts (array)
 */

// 1. <html>, <head>, <body> ouvert et preloader
require_once __DIR__ . '/partials/head.php';
?>
    <!-- ===== Page Wrapper Start ===== -->
    <div class="flex h-screen overflow-hidden">

      <!-- ===== Sidebar ===== -->
      <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

      <!-- ===== Content Area Start ===== -->
      <div class="relative flex flex-1 flex-col overflow-x-hidden overflow-y-auto">
        <main id="main-content">

          <!-- ===== Header ===== -->
          <?php require_once __DIR__ . '/partials/header.php'; ?>

          <!-- ===== Contenu spécifique à la page ===== -->
          <div class="mx-auto max-w-(--breakpoint-2xl) p-4 pb-20 md:p-6 md:pb-6">
            <?= $content ?>
          </div>

          <!-- ===== Footer ===== -->
          <?php require_once __DIR__ . '/partials/footer.php'; ?>

        </main>
      </div>
      <!-- ===== Content Area End ===== -->
    </div>
    <!-- ===== Page Wrapper End ===== -->

<?php
// 7. Scripts et fermeture </body></html>
require_once __DIR__ . '/partials/scripts.php';