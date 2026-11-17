<?php
// Variables fournies par Controller::render() : $title, $currentPage, $content, $extraScripts
require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="container py-4">
    <?= $content ?>
</main>
<?php
require_once __DIR__ . '/includes/footer.php';
require_once __DIR__ . '/includes/scripts.php';