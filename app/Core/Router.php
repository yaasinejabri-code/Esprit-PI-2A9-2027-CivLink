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
        $method = $_GET['action'] ?? $method;
        $class  = 'App\\Controllers\\' . $class;

        $obj = new $class();
        if (!method_exists($obj, $method) || !is_callable([$obj, $method])) {
            http_response_code(404);
            echo '404 - Action introuvable';
            return;
        }
        $obj->$method();
    }
}