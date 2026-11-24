<?php
/**
 * Views/BackOffice/partials/sidebar.php
 * Menu latéral. Attend : $currentPage (string)
 * Le lien actif est calculé en PHP en comparant $currentPage à la clé de chaque lien.
 */
$assets = $assets ?? '/CivLink/Views/BackOffice/assets/';
$base   = '/CivLink/Views/BackOffice/index.php?page=';

// Petite fonction utilitaire : ce lien est-il celui de la page courante ?
if (!function_exists('isActive')) {
    function isActive(string $key, ?string $currentPage): bool
    {
        return $key === $currentPage;
    }
}

// Pour ajouter une page au menu, il suffit d'ajouter un bloc ici.
$menuItems = [
    [
        'key'   => 'dashboard',
        'label' => 'Tableau de bord',
        'icon'  => '<rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/>',
    ],
    [
        'key'   => 'users',
        'label' => 'Utilisateurs',
        'icon'  => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><path d="M16 4.7a3.5 3.5 0 0 1 0 6.6M18 14.3c2 .7 3.5 2.6 3.5 5.7"/>',
    ],
    [
        'key'   => 'administrations',
        'label' => 'Administrations',
        'icon'  => '<path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h1M14 10h1M9 14h1M14 14h1M10 21v-4h4v4"/>',
    ],
    [
        'key'   => 'demarches',
        'label' => 'Démarches et documents',
        'icon'  => '<path d="M8 3h9l4 4v14H3V3h5M14 3v6h7M7 13h10M7 17h8"/>',
    ],
    [
        'key'   => 'signalements',
        'label' => 'Signalements',
        'icon'  => '<path d="M12 22s8-4.5 8-12a8 8 0 1 0-16 0c0 7.5 8 12 8 12Z"/><circle cx="12" cy="10" r="2.5"/>',
    ],
    [
        'key'   => 'factures',
        'label' => 'Factures et paiements',
        'icon'  => '<path d="M5 3h14v18l-3-2-4 2-4-2-3 2V3Z"/><path d="M8 8h8M8 12h8M8 16h5"/>',
    ],
    [
        'key'   => 'actualites',
        'label' => 'Actualités',
        'icon'  => '<rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M7 7h10M7 11h10M7 15h6"/>',
    ],
    [
        'key'   => 'rendezvous',
        'label' => 'Rendez-vous',
        'icon'  => '<rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path d="M7 3v4M17 3v4M3 10h18"/>
                    <path d="m9 15 2 2 4-4"/>',
    ],
    [
        'key'   => 'profile',
        'label' => 'Mon profil',
        'icon'  => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/>',
    ],
];
?>
<!-- Lien pour les lecteurs d'écran -->
<a class="sr-only" href="#main-content">Aller au contenu principal</a>

<!-- Fond sombre (mobile) -->
<div
  x-cloak
  x-show="sidebarToggle"
  class="fixed inset-0 z-99 bg-gray-900/50 backdrop-blur-xs xl:hidden"
  aria-hidden="true"
  @click="sidebarToggle = false"
  x-transition.opacity
></div>

<aside
  x-cloak
  :class="sidebarToggle ? 'translate-x-0 xl:w-22.5' : '-translate-x-full rtl:translate-x-full'"
  class="sidebar fixed top-0 left-0 z-9999 flex h-screen w-72.5 flex-col border-r border-gray-200 bg-white px-5 transition-all duration-300 xl:static xl:translate-x-0 rtl:right-0 rtl:left-auto rtl:border-r-0 rtl:border-l dark:border-gray-800 dark:bg-black"
  @click.outside="if (window.innerWidth < 1280) sidebarToggle = false"
  aria-label="Navigation latérale"
>
  <!-- Logo -->
  <div
    :class="sidebarToggle ? 'xl:justify-center' : 'justify-between'"
    class="sidebar-header flex items-center gap-2 pt-5 pb-8"
  >
    <a href="<?= $base ?>dashboard" class="flex items-center gap-2">
      <span class="sr-only">Accueil</span>
      <span class="flex size-9 shrink-0 items-center justify-center rounded-lg text-lg font-bold text-white" style="background:#f7941d;">C</span>
      <span class="logo text-xl font-bold text-gray-800 dark:text-white" :class="sidebarToggle ? 'xl:hidden' : ''">Civ<span style="color:#f7941d;">Link</span></span>
    </a>
  </div>

  <div class="no-scrollbar flex flex-1 flex-col overflow-y-auto duration-300 ease-linear">
    <nav aria-label="Navigation principale">
      <h3 class="mb-4 text-xs leading-5 text-gray-400 uppercase">
        <span class="menu-group-title" :class="sidebarToggle ? 'xl:hidden' : ''">MENU</span>
      </h3>

      <ul class="mb-6 flex flex-col gap-1">
        <?php foreach ($menuItems as $item): ?>
          <?php $active = isActive($item['key'], $currentPage ?? null); ?>
          <li>
            <a
              href="<?= htmlspecialchars($item['url'] ?? ($base . $item['key']),ENT_QUOTES,'UTF-8') ?>"
              class="group menu-item <?= $active ? 'menu-item-active' : 'menu-item-inactive' ?>"
              <?= $active ? 'aria-current="page"' : '' ?>
            >
              <svg
                class="<?= $active ? 'menu-item-icon-active' : 'menu-item-icon-inactive' ?>"
                width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
              ><?= $item['icon'] ?></svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
              </span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>

      <h3 class="mb-4 text-xs leading-5 text-gray-400 uppercase">
        <span class="menu-group-title" :class="sidebarToggle ? 'xl:hidden' : ''">COMPTE</span>
      </h3>

      <ul class="mb-6 flex flex-col gap-1">
        <li>
          <a href="/CivLink/" class="group menu-item menu-item-inactive">
            <svg class="menu-item-icon-inactive" width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10 6l-6 6 6 6M4 12h16"/>
            </svg>
            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">Retour au site</span>
          </a>
        </li>
        <li>
          <a href="/CivLink/Views/FrontOffice/pages/deconnexion.php" class="group menu-item menu-item-inactive">
            <svg class="menu-item-icon-inactive" width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3M10 16l-4-4 4-4M6 12h10"/>
            </svg>
            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">Déconnexion</span>
          </a>
        </li>
      </ul>
    </nav>
  </div>
</aside>