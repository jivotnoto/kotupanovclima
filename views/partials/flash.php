<?php if (!empty($flash['message'])): ?>
    <div class="flash flash--<?= e($flash['type'] ?? 'info') ?>">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>
