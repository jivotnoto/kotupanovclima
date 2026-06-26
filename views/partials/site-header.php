<?php
$publicLinks = [
    '/' => 'Начало',
    '/promocii' => 'Промоции',
    '/remont-i-profilaktika' => 'Ремонт и профилактика',
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

$brandName = $company['companyName'] ?? 'Котупановклима ЕООД';
?>
<header class="topbar">
    <div class="topbar__inner">
        <div class="topbar__nav-row">
            <details class="mobile-menu">
                <summary class="mobile-menu__button">
                    <span>Меню</span>
                    <span class="mobile-menu__icon" aria-hidden="true"></span>
                </summary>
                <nav class="mobile-menu__panel" aria-label="Основно меню">
                    <?php foreach (($isAdmin ? $adminLinks : $publicLinks) as $href => $label): ?>
                        <a class="mobile-menu__link<?= ($currentPath === $href || starts_with((string) $currentPath, $href . '/')) ? ' is-active' : '' ?>" href="<?= e($href) ?>">
                            <?= e($label) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </details>
        </div>

        <div class="topbar__brand-row">
            <a class="brand brand--site-logo" href="<?= $isAdmin ? '/admin' : '/' ?>" aria-label="<?= e($isAdmin ? $brandName . ' - администрация' : $brandName) ?>">
                <span class="brand__logo-shell">
                    <img class="brand__logo" src="/images/kotupanovclima-logo-transparent.png" alt="">
                </span>
                <?php if ($isAdmin): ?>
                    <span class="brand__admin-label">Администрация</span>
                <?php endif; ?>
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
