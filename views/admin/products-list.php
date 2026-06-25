<section class="admin-panel">
    <div class="admin-toolbar">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Продукти</span>
            <h1 class="section-heading__title">Управление на каталога</h1>
        </div>
        <div class="button-row button-row--wrap">
            <a class="button button--primary" href="/admin/products/new?category=airConditioners">Нов климатик</a>
            <a class="button button--primary" href="/admin/products/new?category=heatPumps">Нова термопомпа</a>
        </div>
    </div>
    <?php $this->partial('partials/flash', ['flash' => $flash]); ?>

    <?php foreach (['airConditioners' => $airProducts, 'heatPumps' => $heatProducts] as $categoryKey => $items): ?>
        <div class="admin-list-card">
            <h2><?= $categoryKey === 'heatPumps' ? 'Термопомпи' : 'Климатици' ?></h2>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>Марка</th>
                        <th>Модел</th>
                        <th>Цена</th>
                        <th>Статус</th>
                        <th>Ред</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $itemIndex => $item): ?>
                        <tr>
                            <td><?= e($item['brand']) ?></td>
                            <td><?= e($item['title']) ?></td>
                            <td>
                                <strong><?= e(format_price_eur($item['priceEur'])) ?></strong>
                                <div class="table-subvalue"><?= e(format_price_bgn($item['priceBgn'])) ?></div>
                            </td>
                            <td><?= e($item['status']) ?></td>
                            <td>
                                <div class="table-order-actions">
                                    <form method="post" action="/admin/products/reorder">
                                        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                        <input type="hidden" name="category" value="<?= e($categoryKey) ?>">
                                        <input type="hidden" name="slug" value="<?= e($item['slug']) ?>">
                                        <input type="hidden" name="direction" value="up">
                                        <button class="button button--compact" type="submit"<?= $itemIndex === 0 ? ' disabled' : '' ?>>Нагоре</button>
                                    </form>
                                    <form method="post" action="/admin/products/reorder">
                                        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                        <input type="hidden" name="category" value="<?= e($categoryKey) ?>">
                                        <input type="hidden" name="slug" value="<?= e($item['slug']) ?>">
                                        <input type="hidden" name="direction" value="down">
                                        <button class="button button--compact" type="submit"<?= $itemIndex === count($items) - 1 ? ' disabled' : '' ?>>Надолу</button>
                                    </form>
                                </div>
                            </td>
                            <td class="table-actions">
                                <a class="button" href="/admin/products/edit?category=<?= e($categoryKey) ?>&slug=<?= e($item['slug']) ?>">Редакция</a>
                                <form method="post" action="/admin/products/delete" onsubmit="return confirm('Сигурен ли си, че искаш да изтриеш продукта?');">
                                    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                    <input type="hidden" name="category" value="<?= e($categoryKey) ?>">
                                    <input type="hidden" name="slug" value="<?= e($item['slug']) ?>">
                                    <button class="button button--danger" type="submit">Изтрий</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</section>
