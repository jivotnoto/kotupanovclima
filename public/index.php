<?php

declare(strict_types=1);

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);

require_once dirname(__DIR__) . '/src/App.php';

$app = new App(dirname(__DIR__));
$app->run();
