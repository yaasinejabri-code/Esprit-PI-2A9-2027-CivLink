<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../app/Core/autoload.php';
require __DIR__ . '/../app/Core/helpers.php';
App\Core\Router::dispatch('back', 'dashboard');
