<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>
<main class="main-wrapper">
    <?php require_once __DIR__ . '/partials/header.php'; ?>
    <?= $content ?>
    <?php require_once __DIR__ . '/partials/footer.php'; ?>
</main>
<?php require_once __DIR__ . '/partials/scripts.php'; ?>
