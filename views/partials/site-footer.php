<?php if (!$isAdmin): ?>
<footer class="site-footer">
    <div class="site-footer__inner">
        <div>
            <h3><?= e($company['companyName'] ?? '') ?></h3>
            <p>Продажба, консултация, монтаж и подбор на климатични системи за дома и бизнеса.</p>
        </div>
        <div>
            <h4>Контакти</h4>
            <p><?= e(($company['phones'][0] ?? '')) ?></p>
            <p><?= e(($company['phones'][1] ?? '')) ?></p>
            <p><?= e($company['address'] ?? '') ?></p>
        </div>
        <div>
            <h4>Регистрация</h4>
            <p>ЕИК: <?= e($company['bulstat'] ?? '') ?></p>
            <p>ДДС: <?= e($company['vatNumber'] ?? '') ?></p>
            <p>Управител: <?= e($company['contactPerson'] ?? '') ?></p>
        </div>
    </div>
</footer>
<?php endif; ?>
