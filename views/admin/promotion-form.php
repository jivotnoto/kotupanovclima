<?php $promotion = $existing ?? []; ?>
<section class="admin-panel">
    <div class="admin-toolbar">
        <div class="section-heading">
            <span class="section-heading__eyebrow"><?= $existing ? 'Редакция' : 'Нова промоция' ?></span>
            <h1 class="section-heading__title"><?= e($pageTitle) ?></h1>
        </div>
        <a class="button" href="/admin/promotions">Назад</a>
    </div>
    <?php $this->partial('partials/flash', ['flash' => $flash]); ?>
    <form class="form-card" method="post" action="/admin/promotions/save">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <input type="hidden" name="currentId" value="<?= e($promotion['id'] ?? '') ?>">
        <div class="notice-card">
            Въвеждаш промо цената в евро. Сайтът автоматично я записва и като левова стойност по курс 1.95583.
        </div>
        <div class="form-grid">
            <div class="field"><label>Заглавие</label><input type="text" name="title" value="<?= e($promotion['title'] ?? '') ?>" required></div>
            <div class="field">
                <label>Категория</label>
                <select name="category">
                    <?php foreach (['general' => 'Обща', 'airConditioners' => 'Климатици', 'heatPumps' => 'Термопомпи'] as $option => $label): ?>
                        <option value="<?= e($option) ?>"<?= (($promotion['category'] ?? 'general') === $option) ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Етикет</label><input type="text" name="badge" value="<?= e($promotion['badge'] ?? '') ?>" placeholder="Напр. Промоция"></div>
            <div class="field">
                <label>Промо цена (EUR)</label>
                <input type="number" step="0.01" name="promoPriceEur" data-eur-input="promotion-price-preview" value="<?= e(($promotion['promoPriceBgn'] ?? null) ? (string) convert_bgn_to_eur((float) $promotion['promoPriceBgn']) : '') ?>">
                <p class="price-preview">Левова стойност: <strong id="promotion-price-preview"><?= e(format_price_bgn(isset($promotion['promoPriceBgn']) ? (float) $promotion['promoPriceBgn'] : null)) ?></strong></p>
            </div>
            <div class="field">
                <label>Стара цена (EUR)</label>
                <input type="number" step="0.01" name="oldPriceEur" data-eur-input="promotion-old-price-preview" value="<?= e(($promotion['oldPriceBgn'] ?? null) ? (string) convert_bgn_to_eur((float) $promotion['oldPriceBgn']) : '') ?>">
                <p class="price-preview">Левова стойност: <strong id="promotion-old-price-preview"><?= e(format_price_bgn(isset($promotion['oldPriceBgn']) ? (float) $promotion['oldPriceBgn'] : null)) ?></strong></p>
            </div>
            <div class="field"><label>Текст на бутона</label><input type="text" name="ctaLabel" value="<?= e($promotion['ctaLabel'] ?? '') ?>" placeholder="Напр. Виж продукта"></div>
            <div class="field"><label>Линк на бутона</label><input type="text" name="ctaHref" value="<?= e($promotion['ctaHref'] ?? '') ?>" placeholder="/produkti/klimatici/..."></div>
            <div class="field"><label>Подредба</label><input type="number" name="sortOrder" value="<?= e((string) ($promotion['sortOrder'] ?? 99)) ?>"></div>
            <div class="field"><label>Активна</label><label class="checkbox"><input type="checkbox" name="isActive"<?= !empty($promotion['isActive']) ? ' checked' : '' ?>> Показвай в сайта</label></div>
        </div>
        <div class="field"><label>Подзаглавие</label><textarea name="subtitle" rows="3"><?= e($promotion['subtitle'] ?? '') ?></textarea></div>
        <div class="field"><label>Кратък акцент</label><textarea name="highlight" rows="2"><?= e($promotion['highlight'] ?? '') ?></textarea></div>
        <div class="field"><label>Бележки (по една на ред)</label><textarea name="notes" rows="5"><?= e(implode("\n", $promotion['notes'] ?? [])) ?></textarea></div>
        <div class="button-row button-row--wrap">
            <button class="button button--primary" type="submit">Запиши промоцията</button>
            <a class="button" href="/admin/promotions">Отказ</a>
        </div>
    </form>
</section>
