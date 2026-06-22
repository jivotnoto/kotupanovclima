<section class="admin-panel">
    <div class="admin-toolbar">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Табло</span>
            <h1 class="section-heading__title">Администрация на каталога</h1>
        </div>
        <form method="post" action="/admin/logout">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
            <button class="button" type="submit">Изход</button>
        </form>
    </div>
    <?php $this->partial('partials/flash', ['flash' => $flash]); ?>
    <div class="stats-grid">
        <article class="stat-card"><span>Продукти</span><strong><?= e((string) $productsCount) ?></strong></article>
        <article class="stat-card"><span>Промоции</span><strong><?= e((string) $promotionsCount) ?></strong></article>
        <article class="stat-card"><span>Режим на достъп</span><strong><?= e(($settings['accessMode'] ?? 'open') === 'allowlist_only' ? 'Само allowlist' : 'Отворен') ?></strong></article>
    </div>
    <div class="button-row button-row--wrap">
        <a class="button button--primary" href="/admin/products">Продукти</a>
        <a class="button" href="/admin/promotions">Промоции</a>
        <a class="button" href="/admin/settings">Настройки</a>
    </div>
</section>
