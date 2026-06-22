<section class="admin-panel admin-panel--narrow">
    <div class="section-heading">
        <span class="section-heading__eyebrow">Администрация</span>
        <h1 class="section-heading__title">Вход в админ панела</h1>
    </div>
    <?php $this->partial('partials/flash', ['flash' => $flash]); ?>
    <form class="form-card" method="post" action="/admin/login">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <div class="field">
            <label for="admin-code">Код за достъп</label>
            <input id="admin-code" type="password" name="code" autocomplete="current-password" required>
        </div>
        <button class="button button--primary" type="submit">Влез</button>
    </form>
</section>
