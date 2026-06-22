<section class="admin-panel">
    <div class="admin-toolbar">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Промоции</span>
            <h1 class="section-heading__title">Управление на офертите</h1>
        </div>
        <a class="button button--primary" href="/admin/promotions/new">Нова промоция</a>
    </div>
    <?php $this->partial('partials/flash', ['flash' => $flash]); ?>
    <div class="admin-list-card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>Заглавие</th>
                    <th>Категория</th>
                    <th>Промо цена</th>
                    <th>Активна</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($promotions as $item): ?>
                    <tr>
                        <td><?= e($item['title']) ?></td>
                        <td><?= e($item['category']) ?></td>
                        <td>
                            <strong><?= e(format_price_eur(convert_bgn_to_eur(isset($item['promoPriceBgn']) ? (float) $item['promoPriceBgn'] : null))) ?></strong>
                            <div class="table-subvalue"><?= e(format_price_bgn(isset($item['promoPriceBgn']) ? (float) $item['promoPriceBgn'] : null)) ?></div>
                        </td>
                        <td><?= !empty($item['isActive']) ? 'Да' : 'Не' ?></td>
                        <td class="table-actions">
                            <a class="button" href="/admin/promotions/edit?id=<?= e($item['id']) ?>">Редакция</a>
                            <form method="post" action="/admin/promotions/delete" onsubmit="return confirm('Сигурен ли си, че искаш да изтриеш промоцията?');">
                                <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                <input type="hidden" name="currentId" value="<?= e($item['id']) ?>">
                                <button class="button button--danger" type="submit">Изтрий</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
