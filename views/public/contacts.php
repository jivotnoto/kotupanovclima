<section class="section-block">
    <div class="two-column">
        <article class="section-surface section-surface--decor">
            <img class="surface-mark surface-mark--contact" src="/images/contact-mark.svg" alt="" aria-hidden="true">
            <div class="section-heading">
                <span class="section-heading__eyebrow">Контакти</span>
                <h1 class="section-heading__title">Свържи се с нас</h1>
            </div>
            <div class="contact-list">
                <div><strong>Телефони</strong><span><?= e(implode(' / ', $company['phones'] ?? [])) ?></span></div>
                <div><strong>Адрес</strong><span><?= e($company['address'] ?? '') ?></span></div>
                <?php if (!empty($company['email'])): ?>
                    <div><strong>Имейл</strong><span><a href="mailto:<?= e($company['email']) ?>"><?= e($company['email']) ?></a></span></div>
                <?php endif; ?>
                <div><strong>Работно време</strong><span><?= e($company['workingHours'] ?? 'По уговорка') ?></span></div>
            </div>
            <div class="nap-block">
                <address>
                    <strong><?= e($company['companyName'] ?? 'Котупановклима ЕООД') ?></strong><br>
                    ул. Китка 3, гр. Перник 2300<br>
                    тел. <?= e($company['phones'][0] ?? '') ?>
                </address>
                <a class="button" href="https://www.google.com/maps/search/?api=1&amp;query=<?= e(rawurlencode('Котупановклима, ул. Китка 3, Перник')) ?>" target="_blank" rel="noopener noreferrer">Виж на Google Карти</a>
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

<section class="section-block">
    <div class="two-column">
        <article class="form-card contact-form-card">
            <div class="section-heading">
                <span class="section-heading__eyebrow">Бързо запитване</span>
                <h2 class="section-heading__title">Изпрати съобщение директно от сайта</h2>
                <p>Опиши накратко помещението, модела или проблема. Ще върнем отговор с насока за следваща стъпка.</p>
            </div>

            <?php $this->partial('partials/flash', ['flash' => $flash]); ?>

            <form class="form-grid" method="post" action="/kontakti">
                <input type="hidden" name="_csrf" value="<?= e($csrfToken ?? '') ?>">
                <div class="field honeypot-field" aria-hidden="true">
                    <label for="website-field">Уебсайт</label>
                    <input id="website-field" type="text" name="website" tabindex="-1" autocomplete="off">
                </div>
                <div class="field field--span-2">
                    <label for="topic-field">Тема</label>
                    <select id="topic-field" name="topic">
                        <?php foreach (($contactTopics ?? []) as $key => $label): ?>
                            <option value="<?= e($key) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="name-field">Име *</label>
                    <input id="name-field" type="text" name="name" maxlength="100" required autocomplete="name">
                </div>
                <div class="field">
                    <label for="phone-field">Телефон</label>
                    <input id="phone-field" type="tel" name="phone" maxlength="70" autocomplete="tel">
                </div>
                <div class="field field--span-2">
                    <label for="email-field">Имейл</label>
                    <input id="email-field" type="email" name="email" maxlength="160" autocomplete="email">
                </div>
                <div class="field field--span-2">
                    <label for="message-field">Съобщение *</label>
                    <textarea id="message-field" name="message" rows="6" maxlength="2200" required placeholder="Например: 20 кв.м стая, нужда от климатик за отопление и охлаждане, ориентировъчен бюджет..."></textarea>
                </div>
                <label class="checkbox field--span-2">
                    <input type="checkbox" name="privacyConsent" value="1" required>
                    <span>Съгласен съм данните да бъдат използвани за отговор на запитването според <a href="/politika-za-poveritelnost">Политиката за поверителност</a>.</span>
                </label>
                <div class="button-row field--span-2">
                    <button class="button button--primary" type="submit">Изпрати запитване</button>
                    <a class="button" href="tel:<?= e($company['phones'][0] ?? '') ?>">Или се обади</a>
                </div>
            </form>
        </article>

        <aside class="section-surface section-surface--cool section-surface--decor">
            <img class="surface-mark surface-mark--service" src="/images/service-mark.svg" alt="" aria-hidden="true">
            <div class="section-heading">
                <span class="section-heading__eyebrow">Полезно за оферта</span>
                <h2 class="section-heading__title">Какво да напишеш</h2>
            </div>
            <ul class="bullet-list">
                <li>Квадратура и тип помещение.</li>
                <li>Дали търсиш охлаждане, отопление или и двете.</li>
                <li>Предпочитана марка или ориентировъчен бюджет.</li>
                <li>При ремонт: симптоми, шум, теч, грешка на дисплея или модел на машината.</li>
            </ul>
        </aside>
    </div>
</section>
