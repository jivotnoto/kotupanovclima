<section class="section-block">
    <div class="section-surface section-surface--decor">
        <img class="surface-mark surface-mark--promo" src="/images/promo-mark.svg" alt="" aria-hidden="true">
        <div class="section-heading">
            <h1 class="section-heading__title">Горещи оферти и цени, които няма как да подминеш</h1>
            <p>Премиум селекция: Климатици и термопомпи с включен професионален монтаж и гарантирана ефективност.</p>
        </div>
    </div>
    <div class="promo-grid">
        <?php foreach ($promotions as $promotion): ?>
            <article class="promo-card">
                <div class="promo-card__image">
                    <?php if (!empty($promotion['imagePath'])): ?>
                        <img src="<?= e($promotion['imagePath']) ?>" alt="<?= e($promotion['imageAlt'] ?? $promotion['title']) ?>">
                    <?php else: ?>
                        <img class="promo-card__image-placeholder" src="/images/promo-mark.svg" alt="">
                    <?php endif; ?>
                </div>
                <div class="promo-card__body">
                    <span class="promo-card__badge"><?= e($promotion['badge'] ?? 'Промоция') ?></span>
                    <h3><?= e($promotion['title']) ?></h3>
                    <p><?= e($promotion['subtitle'] ?? '') ?></p>
                    <div class="promo-card__price">
                        <strong><?= e(format_price_eur(convert_bgn_to_eur(isset($promotion['promoPriceBgn']) ? (float) $promotion['promoPriceBgn'] : null))) ?></strong>
                    </div>
                    <?php if (!empty($promotion['oldPriceBgn'])): ?>
                        <p class="strike-price">Стара цена: <?= e(format_price_eur(convert_bgn_to_eur((float) $promotion['oldPriceBgn']))) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($promotion['highlight'])): ?>
                        <div class="promo-card__highlight"><?= e($promotion['highlight']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($promotion['notes'])): ?>
                        <div class="mobile-copy" data-mobile-copy>
                            <div class="mobile-copy__content" data-mobile-copy-content>
                                <ul class="bullet-list bullet-list--compact">
                                    <?php foreach ($promotion['notes'] as $note): ?>
                                        <li><?= e($note) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <button class="text-toggle" type="button" data-mobile-copy-toggle aria-expanded="false" hidden>Виж още</button>
                        </div>
                    <?php endif; ?>
                    <?php $ctaHref = safe_href($promotion['ctaHref'] ?? null); ?>
                    <?php if ($ctaHref !== null): ?>
                        <a class="button button--promo" href="<?= e($ctaHref) ?>"><?= e($promotion['ctaLabel'] ?? 'Виж продукта') ?></a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
