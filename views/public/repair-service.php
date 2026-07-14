<section class="section-block">
    <div class="section-surface section-surface--decor">
        <img class="surface-mark surface-mark--service" src="/images/service-mark.svg" alt="" aria-hidden="true">
        <div class="section-heading">
            <h1 class="section-heading__title">Ремонт и профилактика на климатична техника в Перник</h1>
            <p>Когато климатикът не охлажда, не отоплява, шумен е или започне да мирише, най-важното е проблемът да се хване навреме. Обслужваме Перник и региона с фокус върху реалната причина, а не върху временни решения.</p>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="two-column">
        <article class="section-surface">
            <div class="section-heading">
                <span class="section-heading__eyebrow">Какво включва</span>
                <h2 class="section-heading__title">Пълна диагностика, ремонт и сезонна поддръжка</h2>
            </div>
            <ul class="bullet-list">
                <li>Диагностика при липса на охлаждане или слабо отопление.</li>
                <li>Проверка при теч от вътрешното тяло, проблем с дренажа или обледяване.</li>
                <li>Оглед при шум, вибрации, нестабилна работа или често спиране.</li>
                <li>Профилактика на филтри, топлообменници, вентилатори и кондензна система.</li>
                <li>Почистване за по-добър въздушен поток, по-тиха работа и по-нисък разход.</li>
                <li>Проверка на електрически връзки, работни режими и общо състояние на системата.</li>
            </ul>
        </article>
        <article class="section-surface section-surface--cool">
            <div class="section-heading">
                <span class="section-heading__eyebrow">Кога е време</span>
                <h2 class="section-heading__title">Сигнали, че системата има нужда от намеса</h2>
            </div>
            <ul class="bullet-list">
                <li>Климатикът духа, но не постига желаната температура.</li>
                <li>Появява се неприятна миризма при пускане.</li>
                <li>Чува се нехарактерен шум, пукане или вибрация.</li>
                <li>Има следи от вода, капене или влага около вътрешното тяло.</li>
                <li>Машината тръгва трудно, изключва сама или работи неравномерно.</li>
                <li>Разходът е осезаемо по-висок спрямо обичайното.</li>
            </ul>
        </article>
    </div>
</section>

<section class="section-block">
    <div class="section-surface">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Подход</span>
            <h2 class="section-heading__title">Първо намираме причината, после предлагаме смислено решение</h2>
            <p>При сервизните дейности най-важното е да не се сменят части на сляпо. Затова започваме с преглед на симптомите, режимите на работа и състоянието на основните възли, след което даваме ясна насока какво има смисъл да се направи.</p>
        </div>
        <div class="mini-grid">
            <div class="feature-card">Ясна оценка дали проблемът е в замърсяване, дренаж, управление или износен компонент.</div>
            <div class="feature-card">Практична препоръка дали е достатъчна профилактика или е нужен реален ремонт.</div>
            <div class="feature-card">Фокус върху надеждна работа в сезона, не само върху моментно „тръгване“ на машината.</div>
            <div class="feature-card">Подходящо както за домашни климатици, така и за по-натоварени системи в офиси и обекти.</div>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="section-surface">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Пакети услуги</span>
            <h2 class="section-heading__title">Профилактика и ремонт с ясен обхват</h2>
            <p>Цената зависи от модела, типа система и състоянието ѝ. Свържете се за конкретна оферта — след кратко описание на симптомите даваме ориентировъчна цена предварително.</p>
        </div>
        <div class="mini-grid">
            <div class="feature-card"><strong>Сезонна профилактика</strong> — почистване на филтри и топлообменници, проверка на дренаж и работни режими. Цена според модела.</div>
            <div class="feature-card"><strong>Диагностика на проблем</strong> — установяване на причината при слабо охлаждане/отопление, шум, теч или грешка на дисплея.</div>
            <div class="feature-card"><strong>Ремонт</strong> — отстраняване на конкретната повреда след диагностика и съгласуване с клиента.</div>
            <div class="feature-card"><strong>Зареждане с фреон</strong> — проверка за теч и допълване на хладилен агент при нужда.</div>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="section-surface">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Често задавани въпроси</span>
            <h2 class="section-heading__title">Отговори за сервиз и профилактика</h2>
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
    <div class="contact-banner contact-banner--decor">
        <img class="surface-mark surface-mark--contact" src="/images/contact-mark.svg" alt="" aria-hidden="true">
        <div>
            <span class="section-heading__eyebrow">Запитване</span>
            <h2 class="section-heading__title">Опиши проблема и ще насочим какъв тип посещение е най-подходящо</h2>
            <p>Полезно е да споделиш какъв е моделът, как се държи машината, кога се появява проблемът и дали има теч, шум, миризма или загуба на мощност. Това помага да дадем по-точна предварителна насока.</p>
        </div>
        <div class="button-row button-row--stacked">
            <a class="button button--primary" href="tel:<?= e($company['phones'][0] ?? '') ?>">Обади се</a>
            <a class="button" href="/kontakti?topic=repair#barzo-zapitvane">Направете запитване</a>
        </div>
    </div>
</section>
