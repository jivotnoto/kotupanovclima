<section class="hero">
    <div class="hero__content">
        <a class="hero__eyebrow hero__eyebrow--link" href="/promocii"><?= e($settings['promo']['title'] ?? 'Промоции') ?></a>
        <h1 class="hero__title hero__title--compact">Климатична техника, подбрана да изглежда добре и да работи правилно.</h1>
        <p class="hero__lead"><?= e($settings['promo']['subtitle'] ?? '') ?></p>
        <div class="button-row">
            <a class="button button--primary" href="/produkti/klimatici">Разгледай климатици</a>
            <a class="button button--primary" href="/produkti/termopompi">Виж термопомпи</a>
            <a class="button button--primary" href="/promocii">Промоции</a>
        </div>
        <div class="feature-grid">
            <div class="feature-card">Официални марки и подбрани серии</div>
            <div class="feature-card">Стандартен монтаж до 3 м тръбен път</div>
            <div class="feature-card">Консултация според помещението и бюджета</div>
        </div>
    </div>
    <div class="hero__sidebar">
        <div class="stack-card stack-card--brands">
            <h2>Официални марки</h2>
            <p>Подбрани производители за дома, офиса и по-сериозни отоплителни решения.</p>
            <div class="brand-grid">
                <?php foreach ($brandShowcase as $brand): ?>
                    <a class="brand-card" href="/produkti/klimatici">
                        <img src="<?= e($brand['logoPath']) ?>" alt="<?= e($brand['name']) ?>">
                        <div>
                            <strong><?= e($brand['name']) ?></strong>
                            <span><?= e($brand['note']) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="section-heading">
        <span class="section-heading__eyebrow">Промоции</span>
        <h2 class="section-heading__title">Актуални оферти за клиента</h2>
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

<section class="section-block">
    <div class="contact-banner">
        <div>
            <span class="section-heading__eyebrow">Контакти</span>
            <h2 class="section-heading__title">Свържи се за оферта, монтаж или консултация по конкретен модел</h2>
            <p><?= e(($company['companyName'] ?? '') . ', ' . ($company['address'] ?? '')) ?>. Телефони: <?= e(implode(' / ', $company['phones'] ?? [])) ?></p>
        </div>
        <div class="button-row button-row--stacked">
            <a class="button button--primary" href="tel:<?= e($company['phones'][0] ?? '') ?>">Обади се</a>
            <a class="button" href="/kontakti">Пълни контакти</a>
        </div>
    </div>
</section>
