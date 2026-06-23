<?php

$isAdmin = starts_with($currentPath ?? '/', '/admin');
?>
<!doctype html>
<html lang="bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Котупановклима') ?></title>
    <meta name="description" content="Климатици, термопомпи, монтаж и консултация за Перник и региона.">
    <meta name="theme-color" content="#ffffff">
    <link rel="stylesheet" href="/assets/site.css">
    <script src="/assets/site.js" defer></script>
</head>
<body class="<?= $isAdmin ? 'admin-body' : 'site-body' ?>">
<?php $this->partial('partials/site-header', ['company' => $company, 'currentPath' => $currentPath, 'isAdmin' => $isAdmin]); ?>
<main class="<?= $isAdmin ? 'admin-shell' : 'site-shell' ?>">
    <?= $content ?>
</main>
<?php $this->partial('partials/site-footer', ['company' => $company, 'isAdmin' => $isAdmin]); ?>
</body>
</html>
