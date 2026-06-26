<?php

$isAdmin = starts_with($currentPath ?? '/', '/admin');
$metaTitle = $metaTitle ?? ($pageTitle ?? 'Котупановклима');
$metaDescription = $metaDescription ?? 'Климатици, термопомпи, монтаж и консултация за Перник и региона.';
$canonicalPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$seoBaseUrl = 'https://kotupanovclima.eu';
$canonicalUrl = !$isAdmin ? $seoBaseUrl . ($canonicalPath ?: '/') : null;
$absoluteSiteUrl = static function (?string $value) use ($seoBaseUrl): ?string {
    if ($value === null || trim($value) === '') {
        return null;
    }

    $value = trim($value);
    if (str_starts_with($value, '/') && !str_starts_with($value, '//')) {
        return $seoBaseUrl . $value;
    }

    $scheme = strtolower((string) (parse_url($value, PHP_URL_SCHEME) ?? ''));

    return in_array($scheme, ['http', 'https'], true) ? $value : null;
};
$ogTitle = $ogTitle ?? $metaTitle;
$ogDescription = $ogDescription ?? $metaDescription;
$ogType = $ogType ?? 'website';
$ogImageUrl = $absoluteSiteUrl($ogImage ?? '/images/site-og-image.png');
?>
<!doctype html>
<html lang="bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($metaTitle) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <?php if ($isAdmin): ?>
        <meta name="robots" content="noindex, nofollow">
    <?php elseif ($canonicalUrl !== null): ?>
        <link rel="canonical" href="<?= e($canonicalUrl) ?>">
        <meta property="og:locale" content="bg_BG">
        <meta property="og:site_name" content="Котупановклима ЕООД">
        <meta property="og:type" content="<?= e($ogType) ?>">
        <meta property="og:url" content="<?= e($canonicalUrl) ?>">
        <meta property="og:title" content="<?= e($ogTitle) ?>">
        <meta property="og:description" content="<?= e($ogDescription) ?>">
        <?php if ($ogImageUrl !== null): ?>
            <meta property="og:image" content="<?= e($ogImageUrl) ?>">
            <meta property="og:image:alt" content="<?= e($ogTitle) ?>">
        <?php endif; ?>
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?= e($ogTitle) ?>">
        <meta name="twitter:description" content="<?= e($ogDescription) ?>">
        <?php if ($ogImageUrl !== null): ?>
            <meta name="twitter:image" content="<?= e($ogImageUrl) ?>">
        <?php endif; ?>
    <?php endif; ?>
    <meta name="theme-color" content="#ffffff">
    <link rel="icon" type="image/png" sizes="512x512" href="/images/site-icon.png">
    <link rel="apple-touch-icon" href="/images/site-icon.png">
    <link rel="stylesheet" href="/assets/site.css">
    <script src="/assets/site.js" defer></script>
</head>
<body class="<?= $isAdmin ? 'admin-body' : 'site-body' ?>">
<?php $this->partial('partials/site-header', ['company' => $company, 'currentPath' => $currentPath, 'isAdmin' => $isAdmin]); ?>
<main class="<?= $isAdmin ? 'admin-shell' : 'site-shell' ?>">
    <?= $content ?>
</main>
<?php $this->partial('partials/site-footer', ['company' => $company, 'isAdmin' => $isAdmin]); ?>
<?php $this->partial('partials/cookie-consent', ['isAdmin' => $isAdmin]); ?>
</body>
</html>
