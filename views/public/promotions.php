<section class="section-block">
    <div class="section-surface">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Промоции</span>
            <h1 class="section-heading__title">Горещи оферти и цени, които няма как да подминеш</h1>
            <p>Премиум селекция: Климатици и термопомпи с включен професионален монтаж и гарантирана ефективност.</p>
        </div>
    </div>
    <div class="promo-grid">
        <?php foreach ($promotions as $promotion): ?>
            <article class="promo-card">
                <span class="promo-card__badge"><?= e($promotion['badge'] ?? 'Промоция') ?></span>
                <h3><?= e($promotion['title']) ?></h3>
                <p><?= e($promotion['subtitle'] ?? '') ?></p>
                <div class="promo-card__price">
                    <strong><?= e(format_price_eur(convert_bgn_to_eur(isset($promotion['promoPriceBgn']) ? (float) $promotion['promoPriceBgn'] : null))) ?></strong>
                    <span><?= e(format_price_bgn(isset($promotion['promoPriceBgn']) ? (float) $promotion['promoPriceBgn'] : null)) ?></span>
                </div>
                <?php if (!empty($promotion['oldPriceBgn'])): ?>
                    <p class="strike-price">Стара цена: <?= e(format_price_bgn((float) $promotion['oldPriceBgn'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($promotion['highlight'])): ?>
                    <div class="promo-card__highlight"><?= e($promotion['highlight']) ?></div>
                <?php endif; ?>
                <?php if (!empty($promotion['notes'])): ?>
                    <ul class="bullet-list bullet-list--compact">
                        <?php foreach ($promotion['notes'] as $note): ?>
                            <li><?= e($note) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if (!empty($promotion['ctaHref'])): ?>
                    <a class="button" href="<?= e($promotion['ctaHref']) ?>"><?= e($promotion['ctaLabel'] ?? 'Виж продукта') ?></a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
