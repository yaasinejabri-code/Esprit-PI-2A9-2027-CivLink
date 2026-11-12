<?php
namespace App\Core;

class Router
{
    public static function dispatch(string $space, string $default): void
    {
        $routes = require __DIR__ . '/../routes.php';
        $page = $_GET['page'] ?? $default;
        if (!isset($routes[$space][$page])) {
            http_response_code(404);
            echo '404 - Page introuvable';
            return;
        }
        [$class, $method] = $routes[$space][$page];
        $class = 'App\\Controllers\\' . $class;
        (new $class())->$method();
    }
}
