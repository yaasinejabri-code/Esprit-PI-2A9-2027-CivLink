<?php
// Variables fournies par Controller::render() : $title, $currentPage, $content, $extraScripts
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>
<div class="admin-main">
    <?php require_once __DIR__ . '/partials/header.php'; ?>
    <main class="p-4">
        <?= $content ?>
    </main>
    <?php require_once __DIR__ . '/partials/footer.php'; ?>
</div>
<?php require_once __DIR__ . '/partials/scripts.php'; ?>