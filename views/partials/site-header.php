<?php
$publicLinks = [
    '/' => 'Начало',
    '/promocii' => 'Промоции',
    '/produkti/klimatici' => 'Климатици',
    '/produkti/termopompi' => 'Термопомпи',
    '/kontakti' => 'Контакти',
];

$adminLinks = [
    '/admin' => 'Табло',
    '/admin/products' => 'Продукти',
    '/admin/promotions' => 'Промоции',
    '/admin/settings' => 'Настройки',
];
?>
<header class="topbar">
    <div class="topbar__inner">
        <div class="topbar__nav-row">
            <nav class="pill-nav pill-nav--mobile">
                <?php foreach (($isAdmin ? $adminLinks : $publicLinks) as $href => $label): ?>
                    <a class="pill-nav__link<?= ($currentPath === $href || starts_with((string) $currentPath, $href . '/')) ? ' is-active' : '' ?>" href="<?= e($href) ?>">
                        <?= e($label) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <div class="topbar__brand-row">
            <a class="brand" href="<?= $isAdmin ? '/admin' : '/' ?>">
                <span class="brand__badge">KK</span>
                <span class="brand__meta">
                    <span class="brand__title"><?= e($company['companyName'] ?? 'Котупановклима ЕООД') ?></span>
                    <span class="brand__subtitle"><?= $isAdmin ? 'Администрация на сайта' : 'Климатизация и термопомпи' ?></span>
                </span>
            </a>

            <?php if (!$isAdmin): ?>
                <a class="phone-chip phone-chip--desktop" href="tel:<?= e($company['phones'][0] ?? '') ?>">
                    <span>Телефон</span>
                    <strong><?= e($company['phones'][0] ?? '') ?></strong>
                </a>
            <?php endif; ?>
        </div>

        <div class="topbar__utility-row">
            <?php if (!$isAdmin): ?>
                <a class="phone-chip phone-chip--mobile" href="tel:<?= e($company['phones'][0] ?? '') ?>">
                    <span>Обади се</span>
                    <strong><?= e($company['phones'][0] ?? '') ?></strong>
                </a>
                <nav class="pill-nav pill-nav--desktop">
                    <?php foreach ($publicLinks as $href => $label): ?>
                        <a class="pill-nav__link<?= ($currentPath === $href || starts_with((string) $currentPath, $href . '/')) ? ' is-active' : '' ?>" href="<?= e($href) ?>">
                            <?= e($label) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            <?php else: ?>
                <nav class="pill-nav pill-nav--desktop">
                    <?php foreach ($adminLinks as $href => $label): ?>
                        <a class="pill-nav__link<?= ($currentPath === $href || starts_with((string) $currentPath, $href . '/')) ? ' is-active' : '' ?>" href="<?= e($href) ?>">
                            <?= e($label) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</header>
