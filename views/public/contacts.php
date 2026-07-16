<section class="section-block contact-page">
    <article class="section-surface section-surface--decor contact-page__details">
        <img class="surface-mark surface-mark--contact" src="/images/contact-mark.svg" alt="" aria-hidden="true">
        <div class="section-heading">
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
                <?= e($company['address'] ?? 'гр. Перник (2300), Китка 3') ?><br>
                тел. <?= e($company['phones'][0] ?? '') ?>
            </address>
            <?php $mapQuery = rawurlencode(($company['companyName'] ?? 'Котупановклима') . ', ' . ($company['address'] ?? 'гр. Перник (2300), Китка 3')); ?>
            <a class="button" href="https://www.google.com/maps/search/?api=1&amp;query=<?= e($mapQuery) ?>" target="_blank" rel="noopener noreferrer">Виж на Google Карти</a>
        </div>
    </article>

    <article id="barzo-zapitvane" class="form-card contact-form-card">
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
                        <option value="<?= e($key) ?>"<?= (($selectedTopic ?? 'general') === $key) ? ' selected' : '' ?>><?= e($label) ?></option>
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
            <?php if (!empty($turnstileSiteKey)): ?>
                <div class="turnstile-field field--span-2">
                    <div
                        class="cf-turnstile"
                        data-sitekey="<?= e($turnstileSiteKey) ?>"
                        data-theme="light"
                        data-language="bg"
                        data-size="flexible"
                        data-action="contact"
                    ></div>
                    <noscript>За автоматичната проверка е необходимо JavaScript да бъде включен.</noscript>
                </div>
            <?php else: ?>
                <div class="field captcha-field field--span-2">
                    <label for="captcha-answer-field">Проверка: Колко е <?= e($captchaQuestion ?? '') ?>? *</label>
                    <input type="hidden" name="captcha_id" value="<?= e($captchaId ?? '') ?>">
                    <input id="captcha-answer-field" type="text" name="captcha_answer" inputmode="numeric" pattern="[0-9]+" maxlength="3" required autocomplete="off">
                    <small>Тази кратка проверка ограничава автоматичния спам.</small>
                </div>
            <?php endif; ?>
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

    <aside class="section-surface section-surface--decor contact-page__help">
        <img class="surface-mark surface-mark--service" src="/images/service-mark.svg" alt="" aria-hidden="true">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Полезно за оферта</span>
            <h2 class="section-heading__title">Какво да напишеш</h2>
        </div>
        <div class="mobile-copy" data-mobile-copy>
            <div class="mobile-copy__content" data-mobile-copy-content>
                <ul class="bullet-list">
                    <li>Квадратура и тип помещение.</li>
                    <li>Дали търсиш охлаждане, отопление или и двете.</li>
                    <li>Предпочитана марка или ориентировъчен бюджет.</li>
                    <li>При ремонт: симптоми, шум, теч, грешка на дисплея или модел на машината.</li>
                </ul>
            </div>
            <button class="text-toggle" type="button" data-mobile-copy-toggle aria-expanded="false" hidden>Виж още</button>
        </div>
    </aside>
</section>
