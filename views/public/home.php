<?php
$brandCategoryTargets = [
    'LG' => '/produkti/termopompi',
];
?>

<section class="brand-logo-strip" aria-label="Официални марки">
    <div class="brand-logo-strip__viewport">
        <div class="brand-logo-strip__track">
            <?php for ($loop = 0; $loop < 2; $loop++): ?>
                <div class="brand-logo-strip__group"<?= $loop === 1 ? ' aria-hidden="true"' : '' ?>>
                    <?php foreach ($brandShowcase as $brand): ?>
                        <?php
                        $categoryPath = $brandCategoryTargets[$brand['name']] ?? '/produkti/klimatici';
                        $brandHref = $categoryPath . '?' . http_build_query(['brand' => $brand['name']]);
                        ?>
                        <a class="brand-logo-strip__item" href="<?= e($brandHref) ?>" aria-label="Виж продукти <?= e($brand['name']) ?>"<?= $loop === 1 ? ' tabindex="-1"' : '' ?>>
                            <img src="<?= e($brand['logoPath']) ?>" alt="<?= e($brand['name']) ?>">
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<section class="hero hero--home">
    <div class="hero__content hero__main-card">
        <img class="hero__visual-mark" src="/images/air-conditioner-mark.svg" alt="" aria-hidden="true">
        <a class="hero__eyebrow hero__eyebrow--link" href="/promocii"><?= e($settings['promo']['title'] ?? 'Промоции') ?></a>
        <h1 class="hero__title hero__title--compact">Климатици и термопомпи в Перник — продажба, монтаж и сервиз</h1>
        <p class="hero__subtitle">Климатична техника, подбрана да изглежда добре и да работи правилно.</p>
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
                <?php $ctaHref = safe_href($promotion['ctaHref'] ?? null); ?>
                <?php if ($ctaHref !== null): ?>
                    <a class="button" href="<?= e($ctaHref) ?>"><?= e($promotion['ctaLabel'] ?? 'Виж продукта') ?></a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section-block">
    <div class="section-surface">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Защо да изберете нас</span>
            <h2 class="section-heading__title">Климатици и термопомпи с монтаж и сервиз в Перник и региона</h2>
            <p>Предлагаме продажба, монтаж, ремонт и профилактика на <a href="/produkti/klimatici">климатици</a> и <a href="/produkti/termopompi">термопомпи</a> за домове и офиси в Перник, Радомир, Батановци и околните населени места. Работим само с официални марки, даваме гаранция и ясна консултация според помещението и бюджета.</p>
        </div>
        <div class="mini-grid">
            <div class="feature-card">Официални марки и подбрани серии с реални технически данни.</div>
            <div class="feature-card">Стандартен монтаж до 3 м тръбен път, включен в цената на много модели.</div>
            <div class="feature-card">Сервиз, диагностика и сезонна профилактика за надеждна работа.</div>
            <div class="feature-card">Консултация за точния модел според квадратура, изложение и нужди.</div>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="section-surface">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Често задавани въпроси</span>
            <h2 class="section-heading__title">Отговори за монтаж, профилактика и избор</h2>
        </div>
        <div class="faq-list">
            <?php foreach (($faq ?? []) as $item): ?>
                <details class="faq-item">
                    <summary><?= e($item['question']) ?></summary>
                    <p><?= e($item['answer']) ?></p>
                </details>
            <?php endforeach; ?>
        </div>
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
