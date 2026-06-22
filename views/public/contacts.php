<section class="section-block">
    <div class="two-column">
        <article class="section-surface">
            <div class="section-heading">
                <span class="section-heading__eyebrow">Контакти</span>
                <h1 class="section-heading__title">Свържи се с нас</h1>
            </div>
            <div class="contact-list">
                <div><strong>Телефони</strong><span><?= e(implode(' / ', $company['phones'] ?? [])) ?></span></div>
                <div><strong>Адрес</strong><span><?= e($company['address'] ?? '') ?></span></div>
                <div><strong>Имейл</strong><span><?= e($company['email'] ?? 'Ще бъде добавен в следващата стъпка') ?></span></div>
                <div><strong>Работно време</strong><span><?= e($company['workingHours'] ?? 'По уговорка') ?></span></div>
            </div>
        </article>
        <article class="section-surface section-surface--cool">
            <div class="section-heading">
                <span class="section-heading__eyebrow">Какво можеш да заявиш</span>
                <h2 class="section-heading__title">Консултация, избор и монтаж</h2>
            </div>
            <ul class="bullet-list">
                <li>Консултация за избор на климатик или термопомпа според помещението и бюджета.</li>
                <li>Запитване за монтаж, подмяна на стара система или базова ценова оферта.</li>
                <li>Регистрационни данни: ЕИК <?= e($company['bulstat'] ?? '') ?>, ДДС <?= e($company['vatNumber'] ?? '') ?>.</li>
            </ul>
        </article>
    </div>
</section>
