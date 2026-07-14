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

$links = $isAdmin ? $adminLinks : $publicLinks;
$brandName = $company['companyName'] ?? 'Котупановклима ЕООД';
$phone = $company['phones'][0] ?? '';
?>
<header class="topbar">
    <div class="topbar__inner">
        <div class="topbar__row">
            <a class="brand brand--site-logo" href="<?= $isAdmin ? '/admin' : '/' ?>" aria-label="<?= e($isAdmin ? $brandName . ' - администрация' : $brandName) ?>">
                <span class="brand__logo-shell">
                    <img class="brand__logo" src="/images/kotupanovclima-logo-transparent.png" alt="">
                </span>
                <?php if ($isAdmin): ?>
                    <span class="brand__admin-label">Администрация</span>
                <?php endif; ?>
            </a>

            <nav class="pill-nav pill-nav--desktop" aria-label="Основно меню">
                <?php foreach ($links as $href => $label): ?>
                    <a class="pill-nav__link<?= ($currentPath === $href || starts_with((string) $currentPath, $href . '/')) ? ' is-active' : '' ?>" href="<?= e($href) ?>">
                        <?= e($label) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php if (!$isAdmin && $phone !== ''): ?>
                <a class="phone-chip phone-chip--desktop" href="tel:<?= e($phone) ?>">
                    <span>Телефон</span>
                    <strong><?= e($phone) ?></strong>
                </a>
            <?php endif; ?>

            <details class="mobile-menu">
                <summary class="mobile-menu__button" aria-label="Отвори основното меню">
                    <span class="visually-hidden">Меню</span>
                    <span class="mobile-menu__icon" aria-hidden="true"></span>
                </summary>
                <nav class="mobile-menu__panel" aria-label="Мобилно меню">
                    <?php foreach ($links as $href => $label): ?>
                        <a class="mobile-menu__link<?= ($currentPath === $href || starts_with((string) $currentPath, $href . '/')) ? ' is-active' : '' ?>" href="<?= e($href) ?>">
                            <?= e($label) ?>
                        </a>
                    <?php endforeach; ?>
                    <?php if (!$isAdmin && $phone !== ''): ?>
                        <a class="phone-chip phone-chip--mobile" href="tel:<?= e($phone) ?>">
                            <span>Обади се</span>
                            <strong><?= e($phone) ?></strong>
                        </a>
                    <?php endif; ?>
                </nav>
            </details>
        </div>
    </div>
</header>
