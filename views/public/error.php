<section class="section-block">
    <div class="section-surface">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Грешка <?= e((string) $statusCode) ?></span>
            <h1 class="section-heading__title"><?= e($pageTitle) ?></h1>
            <p><?= e($message) ?></p>
        </div>
        <div class="button-row">
            <a class="button button--primary" href="/">Към началната страница</a>
            <a class="button" href="/kontakti">Контакти</a>
        </div>
    </div>
</section>
