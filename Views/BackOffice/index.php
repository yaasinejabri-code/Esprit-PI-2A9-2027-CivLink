<?php
declare(strict_types=1);

/**
 * Views/BackOffice/index.php
 * Point d'entrée unique du back-office : ?page=dashboard
 */

// 1. En-têtes HTTP de sécurité
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-store');

// 2. Session sécurisée (celle de tes amis) + expiration après 30 min d'inactivité
require_once __DIR__ . '/../../Models/Auth.php';
authStart();

$sessionLifetime = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionLifetime)) {
    $_SESSION = [];
    session_destroy();
    header('Location: /CivLink/Views/FrontOffice/pages/authentification.php');
    exit;
}
$_SESSION['last_activity'] = time();
$routes = require __DIR__ . '/../../Controllers/routes.php';

$page = $_GET['page'] ?? 'dashboard';

// Seules les pages listées dans routes.php sont autorisées
if (!is_string($page) || !isset($routes[$page])) {
    http_response_code(404);
    die('404 - Page introuvable');
}

[$controllerName, $method] = $routes[$page];

require_once __DIR__ . '/../../Controllers/' . $controllerName . '.php';

(new $controllerName())->$method();