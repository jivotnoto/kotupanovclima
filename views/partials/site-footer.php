<?php if (!$isAdmin): ?>
<footer class="site-footer">
    <div class="site-footer__inner">
        <div class="site-footer__brand">
            <h3><?= e($company['companyName'] ?? '') ?></h3>
            <p>Продажба, консултация, монтаж и подбор на климатични системи за дома и бизнеса.</p>
        </div>
        <nav class="site-footer__column" aria-label="Бързи връзки">
            <h4>Навигация</h4>
            <a href="/">Начало</a>
            <a href="/produkti/klimatici">Климатици</a>
            <a href="/produkti/termopompi">Термопомпи</a>
            <a href="/promocii">Промоции</a>
            <a href="/remont-i-profilaktika">Ремонт и профилактика</a>
        </nav>
        <div class="site-footer__column">
            <h4>Контакти</h4>
            <?php foreach (($company['phones'] ?? []) as $phone): ?>
                <a href="tel:<?= e($phone) ?>"><?= e($phone) ?></a>
            <?php endforeach; ?>
            <?php if (!empty($company['email'])): ?>
                <a href="mailto:<?= e($company['email']) ?>"><?= e($company['email']) ?></a>
            <?php endif; ?>
            <p><?= e($company['address'] ?? '') ?></p>
        </div>
        <div class="site-footer__column">
            <h4>Регистрация</h4>
            <p>ЕИК: <?= e($company['bulstat'] ?? '') ?></p>
            <p>ДДС: <?= e($company['vatNumber'] ?? '') ?></p>
            <p>Управител: <?= e($company['contactPerson'] ?? '') ?></p>
        </div>
        <div class="site-footer__bottom">
            <span>© <?= e(date('Y')) ?> <?= e($company['companyName'] ?? 'Котупановклима ЕООД') ?></span>
            <a href="/obshti-usloviya">Общи условия</a>
            <a href="/politika-za-poveritelnost">Политика за поверителност</a>
            <a href="/kontakti">Контакти</a>
        </div>
    </div>
</footer>
<?php endif; ?>
