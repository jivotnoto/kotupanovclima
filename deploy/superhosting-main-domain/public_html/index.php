<?php

declare(strict_types=1);

// If you deploy the application above public_html with another folder name,
// change only this path.
$appBasePath = dirname(__DIR__) . '/kotupanovklima-app';

require_once $appBasePath . '/bootstrap.php';

$app = boot_kotupanovklima($appBasePath);
$app->run();
