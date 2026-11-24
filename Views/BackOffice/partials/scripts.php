<?php
/**
 * Views/BackOffice/partials/scripts.php
 * Fin de page. Attend (optionnel) : $extraScripts (tableau d'URL de scripts propres à la page)
 */
foreach (($extraScripts ?? []) as $src): ?>
    <script src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>
  </body>
</html>