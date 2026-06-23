<?php

declare(strict_types=1);

$basePath = dirname(__DIR__);

require_once $basePath . '/bootstrap.php';

$app = boot_kotupanovklima($basePath);
$app->run();
