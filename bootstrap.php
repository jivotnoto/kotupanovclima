<?php

declare(strict_types=1);

function boot_kotupanovklima(string $basePath): App
{
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);

    require_once rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'App.php';

    return new App($basePath);
}
