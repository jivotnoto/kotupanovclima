<?php
$categoryPath = $category === 'heatPumps' ? 'termopompi' : 'klimatici';
$query = mb_strtolower(trim((string) ($_GET['q'] ?? '')), 'UTF-8');
$brandFilter = trim((string) ($_GET['brand'] ?? ''));
$technologyFilter = trim((string) ($_GET['technology'] ?? ''));
$powerFilter = trim((string) ($_GET['power'] ?? ''));
$parsePrice = static function (mixed $value): ?float {
    $value = str_replace(',', '.', trim((string) $value));

    return $value !== '' && is_numeric($value) && (float) $value >= 0 ? (float) $value : null;
};
$minPrice = $parsePrice($_GET['minPrice'] ?? null);
$maxPrice = $parsePrice($_GET['maxPrice'] ?? null);
if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
    [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
}

$filtered = array_values(array_filter($products, static function (array $product) use ($query, $brandFilter, $technologyFilter, $powerFilter, $minPrice, $maxPrice): bool {
    $matchesQuery = $query === '' || str_contains(mb_strtolower($product['title'] . ' ' . $product['series'] . ' ' . $product['brand'], 'UTF-8'), $query);
    $matchesBrand = $brandFilter === '' || $product['brand'] === $brandFilter;
    $matchesTechnology = $technologyFilter === '' || $product['technology'] === $technologyFilter;
    $matchesPower = $powerFilter === '' || (string) ($product['btu'] ?? '') === $powerFilter;
    $matchesMinimumPrice = $minPrice === null || ($product['priceEur'] !== null && $product['priceEur'] >= $minPrice);
    $matchesMaximumPrice = $maxPrice === null || ($product['priceEur'] !== null && $product['priceEur'] <= $maxPrice);

    return $matchesQuery && $matchesBrand && $matchesTechnology && $matchesPower && $matchesMinimumPrice && $matchesMaximumPrice;
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
            <?php if ($category !== 'airConditioners'): ?>
                <span class="section-heading__eyebrow"><?= e($pageTitle) ?></span>
            <?php endif; ?>
            <h1 class="section-heading__title"><?= e($title) ?></h1>
            <p><?= e($description) ?></p>
        </div>
    </div>

    <div class="section-surface">
        <?php if ($category === 'heatPumps'): ?>
            <div class="section-heading">
                <span class="section-heading__eyebrow">Гид за избор</span>
                <h2 class="section-heading__title">Как да изберете термопомпа за отопление и охлаждане</h2>
            </div>
            <p>Термопомпите са сред най-икономичните решения за отопление, защото извличат енергия от въздуха и я пренасят в дома, вместо да я произвеждат директно. За жилища в Перник и региона най-често се използват два типа: <strong>въздух-вода</strong> (за отопление, охлаждане и топла вода през водна инсталация, радиатори или подово отопление) и <strong>въздух-въздух</strong> (класически климатик с висок капацитет за отопление).</p>
            <p>При избора е важно мощността (kW) да е съобразена с квадратурата, изолацията и отоплителните нужди на дома, а не просто да е „по-голяма за всеки случай“. Обърнете внимание на коефициента <strong>SCOP</strong> (сезонна ефективност при отопление) — колкото по-висок, толкова по-нисък е разходът за ток. Проверете и работния температурен диапазон: качествените термопомпи поддържат отопление и при външни температури доста под нулата.</p>
            <p>Ориентировъчно решенията въздух-вода за средно жилище започват от около 5 100–7 700 EUR в зависимост от мощността, марката и типа инсталация. За точна оферта е най-добре да направим консултация според конкретния дом. При покупка на нова термопомпа може да проверите и приложимите програми за енергийна ефективност.</p>
        <?php else: ?>
            <div class="section-heading">
                <span class="section-heading__eyebrow">Гид за избор</span>
                <h2 class="section-heading__title">Как да изберете климатик според квадратурата и нуждите</h2>
            </div>
            <p>Първата стъпка при избора на климатик е мощността, измервана в <strong>BTU</strong>. За ориентир: модел <strong>9000 BTU</strong> е подходящ за стая до около 20 кв.м, <strong>12000 BTU</strong> — за 20–35 кв.м, а <strong>18000 BTU</strong> и нагоре — за по-големи помещения или пространства с високи тавани и голямо остъкляване. Изложението, изолацията и броят на прозорците също влияят, затова при съмнение е по-добре да се консултирате.</p>
            <p>Вторият важен фактор е енергийната ефективност — класовете <strong>SEER</strong> (охлаждане) и <strong>SCOP</strong> (отопление). По-високите стойности означават по-нисък разход на ток при същия комфорт. <strong>Инверторните</strong> и <strong>хиперинверторните</strong> модели поддържат по-стабилна температура, работят по-тихо и харчат по-малко от старите неинверторни машини, а хиперинверторните запазват мощността си на отопление и при ниски външни температури.</p>
            <p>Обърнете внимание и на нивото на шум (dB), наличието на Wi-Fi управление и вида на филтрите. Условията за монтаж зависят от избрания модел, дължината на трасето и особеностите на обекта, затова ги уточняваме предварително. Разгледайте моделите по-долу и филтрирайте по марка, технология и мощност, за да стесните избора си бързо.</p>
        <?php endif; ?>
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
            <label for="power-field">Размер / мощност</label>
            <select id="power-field" name="power">
                <option value="">Всички мощности</option>
                <?php foreach ($powers as $power): ?>
                    <option value="<?= e((string) $power) ?>"<?= $powerFilter === (string) $power ? ' selected' : '' ?>><?= e((string) $power) ?> BTU</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="min-price-field">Минимална цена (EUR)</label>
            <input id="min-price-field" type="number" min="0" step="0.01" name="minPrice" value="<?= e($minPrice !== null ? (string) $minPrice : '') ?>" placeholder="От">
        </div>
        <div class="field">
            <label for="max-price-field">Максимална цена (EUR)</label>
            <input id="max-price-field" type="number" min="0" step="0.01" name="maxPrice" value="<?= e($maxPrice !== null ? (string) $maxPrice : '') ?>" placeholder="До">
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
                    <?php if (!empty($product['badges'])): ?>
                        <span class="product-badge-row" aria-label="Акценти за продукта">
                            <?php foreach ($product['badges'] as $badge): ?>
                                <span class="product-badge"><?= e($badge) ?></span>
                            <?php endforeach; ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($product['imagePath'])): ?>
                        <img src="<?= e($product['imagePath']) ?>" alt="<?= e($product['typeLabel'] . ' ' . $product['title'] . ' — цена и монтаж в Перник') ?>" loading="lazy" decoding="async">
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
                        </div>
                    </div>

                    <?php if (!empty($product['description'])): ?>
                        <div class="product-card__description-wrap" data-description-disclosure>
                            <p class="product-card__description" data-description-text><?= e($product['description']) ?></p>
                            <button class="text-toggle" type="button" data-description-toggle aria-expanded="false" hidden>Прочети всичко</button>
                        </div>
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
                    </div>

                    <a class="button button--dark" href="<?= e($productHref) ?>">Виж детайли</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
