<?php
$email = $company['email'] ?? '';
$website = $company['website'] ?? '';
$workingHours = $company['workingHours'] ?? '';
$detectedClientIp = $clientIpContext['clientIp'] ?? null;
$detectedSource = $clientIpContext['clientIpSource'] ?? 'unknown';
$detectedRemoteAddr = $clientIpContext['remoteAddr'] ?? null;
$detectedForwardedFor = $clientIpContext['xForwardedFor'] ?? null;
?>
<section class="admin-panel">
    <div class="admin-toolbar">
        <div class="section-heading">
            <span class="section-heading__eyebrow">Настройки</span>
            <h1 class="section-heading__title">Сайт и администрация</h1>
        </div>
        <a class="button" href="/admin">Назад</a>
    </div>
    <?php $this->partial('partials/flash', ['flash' => $flash]); ?>
    <form class="form-card" method="post" action="/admin/settings/save">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <div class="notice-card">
            Ако включиш режим само с allowlist, сложи текущия си IP адрес в списъка, за да не се заключиш извън админ панела.
            Засеченият IP в момента е <strong><?= e($detectedClientIp ?? 'неизвестен') ?></strong> чрез <strong><?= e($detectedSource) ?></strong>.
            <?php if ($detectedRemoteAddr !== null && $detectedRemoteAddr !== $detectedClientIp): ?>
                Apache вижда REMOTE_ADDR = <strong><?= e($detectedRemoteAddr) ?></strong>.
            <?php endif; ?>
            <?php if ($detectedForwardedFor): ?>
                X-Forwarded-For = <strong><?= e($detectedForwardedFor) ?></strong>.
            <?php endif; ?>
        </div>
        <div class="form-grid">
            <div class="field"><label>Email</label><input type="email" name="email" value="<?= e($email) ?>"></div>
            <div class="field"><label>Website</label><input type="url" name="website" value="<?= e($website) ?>"></div>
            <div class="field"><label>Работно време</label><input type="text" name="workingHours" value="<?= e($workingHours) ?>"></div>
            <div class="field">
                <label>Режим на достъп</label>
                <select name="accessMode">
                    <option value="open"<?= ($settings['accessMode'] ?? 'open') === 'open' ? ' selected' : '' ?>>Отворен достъп</option>
                    <option value="allowlist_only"<?= ($settings['accessMode'] ?? 'open') === 'allowlist_only' ? ' selected' : '' ?>>Само allowlist</option>
                </select>
            </div>
        </div>
        <div class="field">
            <label>Allowed IPs / CIDR (по един на ред)</label>
            <textarea name="allowedIps" rows="4"><?= e(implode("\n", $settings['allowedIps'] ?? [])) ?></textarea>
        </div>
        <div class="field"><label>Промо заглавие</label><input type="text" name="promoTitle" value="<?= e($settings['promo']['title'] ?? '') ?>"></div>
        <div class="field"><label>Промо подзаглавие</label><textarea name="promoSubtitle" rows="3"><?= e($settings['promo']['subtitle'] ?? '') ?></textarea></div>
        <div class="field"><label>Нов админ код</label><input type="password" name="adminCode" placeholder="Остави празно, ако не сменяш кода"></div>
        <div class="button-row button-row--wrap">
            <button class="button button--primary" type="submit">Запиши настройките</button>
            <a class="button" href="/admin">Отказ</a>
        </div>
    </form>
</section>
