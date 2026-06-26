<?php
$categoryPath = $product['category'] === 'heatPumps' ? 'termopompi' : 'klimatici';
$productMark = $product['category'] === 'heatPumps' ? '/images/heat-pump-mark.svg' : '/images/air-conditioner-mark.svg';
?>
<section class="section-block">
    <a class="back-link" href="/produkti/<?= e($categoryPath) ?>">Назад към каталога</a>
    <div class="detail-grid">
        <article class="detail-card">
            <div class="detail-card__hero">
                <?php if (!empty($product['imagePath'])): ?>
                    <button class="detail-card__image-button" type="button" data-image-viewer-open data-image-src="<?= e($product['imagePath']) ?>" data-image-alt="<?= e($product['title']) ?>" aria-label="Покажи снимката по-голяма">
                        <img src="<?= e($product['imagePath']) ?>" alt="<?= e($product['title']) ?>">
                    </button>
                <?php endif; ?>
            </div>
            <span class="product-card__brand"><?= e($product['brand']) ?></span>
            <h1 class="detail-card__title"><?= e($product['title']) ?></h1>
            <p class="detail-card__series">Серия: <?= e($product['series']) ?></p>
            <div class="detail-card__price">
                <strong><?= e(format_price_eur($product['priceEur'])) ?></strong>
                <span><?= e(format_price_bgn($product['priceBgn'])) ?></span>
            </div>
            <p class="detail-card__mounting">Цената включва стандартен монтаж до 3 метра тръбен път.</p>
            <?php if (!empty($product['description'])): ?>
                <p class="detail-card__description"><?= e($product['description']) ?></p>
            <?php endif; ?>

            <div class="detail-spec-grid">
                <div class="mini-card"><span>Тип</span><strong><?= e($product['typeLabel']) ?></strong></div>
                <?php if (!empty($product['btu'])): ?><div class="mini-card"><span>BTU</span><strong><?= e((string) $product['btu']) ?></strong></div><?php endif; ?>
                <?php if (!empty($product['powerKw'])): ?><div class="mini-card"><span>Мощност</span><strong><?= e((string) $product['powerKw']) ?> kW</strong></div><?php endif; ?>
                <?php if (!empty($product['technology'])): ?><div class="mini-card"><span>Технология</span><strong><?= e($product['technology']) ?></strong></div><?php endif; ?>
                <?php if (!empty($product['officialModelCode'])): ?><div class="mini-card"><span>Официален код</span><strong><?= e($product['officialModelCode']) ?></strong></div><?php endif; ?>
                <?php if (!empty($product['nominalCoolingKw']) || !empty($product['nominalHeatingKw'])): ?><div class="mini-card"><span>Номинална мощност</span><strong><?= e(($product['nominalCoolingKw'] ? $product['nominalCoolingKw'] . ' kW охлаждане' : '')) ?><?= e(($product['nominalCoolingKw'] && $product['nominalHeatingKw']) ? ' / ' : '') ?><?= e(($product['nominalHeatingKw'] ? $product['nominalHeatingKw'] . ' kW отопление' : '')) ?></strong></div><?php endif; ?>
                <?php if (!empty($product['seer']) || !empty($product['scop'])): ?><div class="mini-card"><span>SEER / SCOP</span><strong><?= e(($product['seer'] ?? '—') . ' / ' . ($product['scop'] ?? '—')) ?></strong></div><?php endif; ?>
                <?php if (!empty($product['energyCooling']) || !empty($product['energyHeating'])): ?><div class="mini-card"><span>Енергиен клас</span><strong><?= e(($product['energyCooling'] ?? '—') . ' / ' . ($product['energyHeating'] ?? '—')) ?></strong></div><?php endif; ?>
                <?php if (!empty($product['coverageM2'])): ?><div class="mini-card"><span>Покритие</span><strong><?= e($product['coverageM2']) ?></strong></div><?php endif; ?>
                <?php if (!empty($product['refrigerant'])): ?><div class="mini-card"><span>Хладилен агент</span><strong><?= e($product['refrigerant']) ?></strong></div><?php endif; ?>
                <?php if (!empty($product['indoorNoiseDb']) || !empty($product['outdoorNoiseDb'])): ?><div class="mini-card"><span>Шум</span><strong><?= e(trim((string) ($product['indoorNoiseDb'] ?? ''))) ?><?= !empty($product['outdoorNoiseDb']) ? ' / ' . e((string) $product['outdoorNoiseDb']) : '' ?></strong></div><?php endif; ?>
                <?php if (!empty($product['indoorDimensionsMm']) || !empty($product['outdoorDimensionsMm'])): ?><div class="mini-card"><span>Размери</span><strong><?= e(trim((string) ($product['indoorDimensionsMm'] ?? ''))) ?><?= !empty($product['outdoorDimensionsMm']) ? ' / ' . e((string) $product['outdoorDimensionsMm']) : '' ?></strong></div><?php endif; ?>
            </div>
        </article>

        <aside class="detail-side-card detail-side-card--decor">
            <img class="surface-mark surface-mark--product" src="<?= e($productMark) ?>" alt="" aria-hidden="true">
            <h2>Ключови акценти</h2>
            <ul class="bullet-list">
                <?php foreach ($product['features'] as $feature): ?>
                    <li><?= e($feature) ?></li>
                <?php endforeach; ?>
                <li>Стандартен монтаж до 3 метра тръбен път е включен в цената.</li>
            </ul>
            <p class="detail-meta">Статус: <?= e($product['status']) ?></p>
            <?php $sourceUrl = safe_href($product['sourceUrl'] ?? null); ?>
            <?php if ($sourceUrl !== null): ?>
                <p class="detail-meta">
                    Технически данни:
                    <a href="<?= e($sourceUrl) ?>" target="_blank" rel="noopener noreferrer"><?= e($product['sourceTitle'] ?? 'официален източник') ?></a>
                </p>
            <?php endif; ?>
        </aside>
    </div>
</section>

<?php if (!empty($product['imagePath'])): ?>
    <div class="image-viewer" data-image-viewer hidden>
        <button class="image-viewer__backdrop" type="button" data-image-viewer-close aria-label="Затвори увеличената снимка"></button>
        <div class="image-viewer__dialog" role="dialog" aria-modal="true" aria-label="<?= e($product['title']) ?>">
            <button class="image-viewer__close" type="button" data-image-viewer-close aria-label="Затвори">×</button>
            <img data-image-viewer-image src="<?= e($product['imagePath']) ?>" alt="<?= e($product['title']) ?>">
        </div>
    </div>
<?php endif; ?>
