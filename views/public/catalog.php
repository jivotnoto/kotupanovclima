<?php
$categoryPath = $category === 'heatPumps' ? 'termopompi' : 'klimatici';
$query = mb_strtolower(trim((string) ($_GET['q'] ?? '')), 'UTF-8');
$brandFilter = trim((string) ($_GET['brand'] ?? ''));
$technologyFilter = trim((string) ($_GET['technology'] ?? ''));
$powerFilter = trim((string) ($_GET['power'] ?? ''));

$filtered = array_values(array_filter($products, static function (array $product) use ($query, $brandFilter, $technologyFilter, $powerFilter): bool {
    $matchesQuery = $query === '' || str_contains(mb_strtolower($product['title'] . ' ' . $product['series'] . ' ' . $product['brand'], 'UTF-8'), $query);
    $matchesBrand = $brandFilter === '' || $product['brand'] === $brandFilter;
    $matchesTechnology = $technologyFilter === '' || $product['technology'] === $technologyFilter;
    $matchesPower = $powerFilter === '' || (string) ($product['btu'] ?? '') === $powerFilter;

    return $matchesQuery && $matchesBrand && $matchesTechnology && $matchesPower;
}));

$brands = array_values(array_unique(array_map(static fn (array $product): string => $product['brand'], $products)));
sort($brands);
$powers = array_values(array_unique(array_filter(array_map(static fn (array $product): ?int => $product['btu'], $products))));
sort($powers);
$catalogMark = $category === 'heatPumps' ? '/images/heat-pump-mark.svg' : '/images/catalog-mark.svg';
?>
<section class="section-block">
    <div class="section-surface section-surface--decor">
        <img class="surface-mark surface-mark--catalog" src="<?= e($catalogMark) ?>" alt="" aria-hidden="true">
        <div class="section-heading">
            <span class="section-heading__eyebrow"><?= e($pageTitle) ?></span>
            <h1 class="section-heading__title"><?= e($title) ?></h1>
            <p><?= e($description) ?></p>
        </div>
    </div>

    <?php if ($category === 'airConditioners'): ?>
        <div class="section-surface">
            <div class="section-heading">
                <span class="section-heading__eyebrow">Официални сайтове</span>
                <p>Ако искаш да свериш серията с производителя, официалните сайтове на марките са тук, а изборът и покупката остават в каталога по-долу.</p>
            </div>
            <div class="button-row button-row--wrap">
                <?php foreach ($officialLinks as $brand): ?>
                    <a class="button" href="<?= e($brand['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($brand['name']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <form class="filters" method="get" action="">
        <div class="field">
            <label for="search-field">Търсене</label>
            <input id="search-field" type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Търси по марка, серия или модел">
        </div>
        <div class="field">
            <label for="brand-field">Марка</label>
            <select id="brand-field" name="brand">
                <option value="">Всички марки</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?= e($brand) ?>"<?= $brandFilter === $brand ? ' selected' : '' ?>><?= e($brand) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="technology-field">Технология</label>
            <select id="technology-field" name="technology">
                <option value="">Всички технологии</option>
                <option value="inverter"<?= $technologyFilter === 'inverter' ? ' selected' : '' ?>>Инвертор</option>
                <option value="hyperinverter"<?= $technologyFilter === 'hyperinverter' ? ' selected' : '' ?>>Хиперинвертор</option>
                <option value="pending"<?= $technologyFilter === 'pending' ? ' selected' : '' ?>>По каталог</option>
            </select>
        </div>
        <div class="field">
            <label for="power-field">Мощност</label>
            <select id="power-field" name="power">
                <option value="">Всички мощности</option>
                <?php foreach ($powers as $power): ?>
                    <option value="<?= e((string) $power) ?>"<?= $powerFilter === (string) $power ? ' selected' : '' ?>><?= e((string) $power) ?> BTU</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="button-row button-row--wrap">
            <button class="button button--primary" type="submit">Филтрирай</button>
            <a class="button" href="/produkti/<?= e($categoryPath) ?>">Изчисти</a>
        </div>
    </form>

    <p class="results-count">Показани модели: <strong><?= e((string) count($filtered)) ?></strong> от <?= e((string) count($products)) ?></p>

    <div class="product-grid">
        <?php foreach ($filtered as $product): ?>
            <?php $productHref = '/produkti/' . $categoryPath . '/' . $product['slug']; ?>
            <article class="product-card">
                <a class="product-card__image product-card__image--link" href="<?= e($productHref) ?>" aria-label="Виж <?= e($product['title']) ?>">
                    <?php if (!empty($product['imagePath'])): ?>
                        <img src="<?= e($product['imagePath']) ?>" alt="<?= e($product['title']) ?>">
                    <?php else: ?>
                        <div class="product-card__image-placeholder"><?= e($product['brand']) ?></div>
                    <?php endif; ?>
                </a>
                <div class="product-card__body">
                    <div class="product-card__top">
                        <div>
                            <span class="product-card__brand"><?= e($product['brand']) ?></span>
                            <h3><?= e($product['model']) ?></h3>
                            <p><?= e($product['series']) ?></p>
                        </div>
                        <div class="product-card__price">
                            <span><?= e($product['typeLabel']) ?></span>
                            <strong><?= e(format_price_eur($product['priceEur'])) ?></strong>
                            <small><?= e(format_price_bgn($product['priceBgn'])) ?></small>
                        </div>
                    </div>

                    <?php if (!empty($product['description'])): ?>
                        <p class="product-card__description"><?= e($product['description']) ?></p>
                    <?php endif; ?>

                    <div class="mini-grid">
                        <div class="mini-card">
                            <span>Мощност</span>
                            <strong><?= e($product['btu'] ? $product['btu'] . ' BTU' : (($product['powerKw'] ?? null) ? $product['powerKw'] . ' kW' : 'По запитване')) ?></strong>
                        </div>
                        <div class="mini-card">
                            <span>Технология</span>
                            <strong><?= e($product['technology']) ?></strong>
                        </div>
                    </div>

                    <div class="tag-row">
                        <span class="tag">Охлаждане <?= e($product['energyCooling'] ?: 'По каталог') ?></span>
                        <span class="tag">Отопление <?= e($product['energyHeating'] ?: 'По каталог') ?></span>
                        <span class="tag">Монтаж до 3 м</span>
                    </div>

                    <a class="button button--dark" href="<?= e($productHref) ?>">Виж детайли</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
