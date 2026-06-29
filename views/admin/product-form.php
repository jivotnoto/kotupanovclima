<?php
$product = $existing['model'] ?? [];
$brand = $existing['brand'] ?? '';
$series = $existing['series'] ?? '';
$oldSlug = $existing ? slugify($brand . '-' . $series . '-' . ($product['modelLabel'] ?? '')) : '';
$priceEur = convert_bgn_to_eur(isset($product['priceBgn']) && is_numeric($product['priceBgn']) ? (float) $product['priceBgn'] : null);
$existingImage = $product['customImagePath'] ?? null;
?>
<section class="admin-panel">
    <div class="admin-toolbar">
        <div class="section-heading">
            <span class="section-heading__eyebrow"><?= $existing ? 'Редакция' : 'Нов продукт' ?></span>
            <h1 class="section-heading__title"><?= e($pageTitle) ?></h1>
        </div>
        <a class="button" href="/admin/products">Назад</a>
    </div>
    <?php $this->partial('partials/flash', ['flash' => $flash]); ?>
    <form class="form-card" method="post" action="/admin/products/save" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <input type="hidden" name="category" value="<?= e($category) ?>">
        <input type="hidden" name="oldSlug" value="<?= e($oldSlug) ?>">

        <div class="notice-card">
            Попълваш основната цена в евро. Сайтът автоматично записва и левовата стойност по фиксирания курс 1.95583.
        </div>

        <div class="form-section-title">Основни данни</div>
        <div class="form-grid">
            <div class="field"><label>Марка</label><input type="text" name="brand" value="<?= e($brand) ?>" required></div>
            <div class="field"><label>Серия</label><input type="text" name="series" value="<?= e($series) ?>" required></div>
            <div class="field"><label>Модел</label><input type="text" name="modelLabel" value="<?= e($product['modelLabel'] ?? '') ?>" required></div>
            <div class="field">
                <label>Цена (EUR)</label>
                <input type="number" step="0.01" name="priceEur" data-eur-input="product-price-preview" value="<?= e($priceEur !== null ? (string) $priceEur : '') ?>">
                <p class="price-preview">Левова стойност: <strong id="product-price-preview"><?= e(format_price_bgn(isset($product['priceBgn']) && is_numeric($product['priceBgn']) ? (float) $product['priceBgn'] : null)) ?></strong></p>
            </div>
            <div class="field"><label>BTU</label><input type="number" name="btu" value="<?= e((string) ($product['btu'] ?? '')) ?>"></div>
            <div class="field"><label>Мощност (kW)</label><input type="number" step="0.1" name="powerKw" value="<?= e((string) ($product['powerKw'] ?? '')) ?>"></div>
            <div class="field">
                <label>Технология</label>
                <select name="technology">
                    <?php foreach (['inverter', 'hyperinverter', 'pending'] as $option): ?>
                        <option value="<?= e($option) ?>"<?= (($product['technology'] ?? 'pending') === $option) ? ' selected' : '' ?>><?= e($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Тип</label><input type="text" name="type" value="<?= e($product['type'] ?? '') ?>"></div>
            <div class="field"><label>Енергиен клас охлаждане</label><input type="text" name="energyCooling" value="<?= e($product['energyCooling'] ?? '') ?>"></div>
            <div class="field"><label>Енергиен клас отопление</label><input type="text" name="energyHeating" value="<?= e($product['energyHeating'] ?? '') ?>"></div>
            <div class="field field--span-2"><label>Описание</label><textarea name="description" rows="4"><?= e($product['description'] ?? '') ?></textarea></div>
        </div>

        <div class="form-section-title">Технически параметри</div>
        <div class="form-grid">
            <div class="field"><label>Официален код</label><input type="text" name="officialModelCode" value="<?= e($product['officialModelCode'] ?? '') ?>"></div>
            <div class="field"><label>Хладилен агент</label><input type="text" name="refrigerant" value="<?= e($product['refrigerant'] ?? '') ?>"></div>
            <div class="field"><label>Вътрешно тяло</label><input type="text" name="indoorUnit" value="<?= e($product['indoorUnit'] ?? '') ?>"></div>
            <div class="field"><label>Външно тяло</label><input type="text" name="outdoorUnit" value="<?= e($product['outdoorUnit'] ?? '') ?>"></div>
            <div class="field"><label>Номинално охлаждане (kW)</label><input type="number" step="0.1" name="nominalCoolingKw" value="<?= e((string) ($product['nominalCoolingKw'] ?? '')) ?>"></div>
            <div class="field"><label>Номинално отопление (kW)</label><input type="number" step="0.1" name="nominalHeatingKw" value="<?= e((string) ($product['nominalHeatingKw'] ?? '')) ?>"></div>
            <div class="field"><label>SEER</label><input type="number" step="0.1" name="seer" value="<?= e((string) ($product['seer'] ?? '')) ?>"></div>
            <div class="field"><label>SCOP</label><input type="number" step="0.1" name="scop" value="<?= e((string) ($product['scop'] ?? '')) ?>"></div>
            <div class="field"><label>Покритие</label><input type="text" name="coverageM2" value="<?= e($product['coverageM2'] ?? '') ?>"></div>
            <div class="field"><label>Wi-Fi</label><label class="checkbox"><input type="checkbox" name="wifi"<?= !empty($product['wifi']) ? ' checked' : '' ?>> Вграден модул</label></div>
            <div class="field"><label>Статус</label><select name="status"><?php foreach (['draft', 'needs_verification', 'verified'] as $status): ?><option value="<?= e($status) ?>"<?= (($product['status'] ?? 'draft') === $status) ? ' selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label>Шум вътрешно тяло</label><input type="text" name="indoorNoiseDb" value="<?= e($product['indoorNoiseDb'] ?? '') ?>"></div>
            <div class="field"><label>Шум външно тяло</label><input type="text" name="outdoorNoiseDb" value="<?= e($product['outdoorNoiseDb'] ?? '') ?>"></div>
            <div class="field"><label>Размери вътрешно тяло</label><input type="text" name="indoorDimensionsMm" value="<?= e($product['indoorDimensionsMm'] ?? '') ?>"></div>
            <div class="field"><label>Размери външно тяло</label><input type="text" name="outdoorDimensionsMm" value="<?= e($product['outdoorDimensionsMm'] ?? '') ?>"></div>
            <div class="field"><label>Тегло вътрешно тяло</label><input type="number" step="0.1" name="indoorWeightKg" value="<?= e((string) ($product['indoorWeightKg'] ?? '')) ?>"></div>
            <div class="field"><label>Тегло външно тяло</label><input type="number" step="0.1" name="outdoorWeightKg" value="<?= e((string) ($product['outdoorWeightKg'] ?? '')) ?>"></div>
            <div class="field"><label>Диапазон охлаждане</label><input type="text" name="coolingRangeKw" value="<?= e($product['coolingRangeKw'] ?? '') ?>"></div>
            <div class="field"><label>Диапазон отопление</label><input type="text" name="heatingRangeKw" value="<?= e($product['heatingRangeKw'] ?? '') ?>"></div>
            <div class="field"><label>Входяща мощност охлаждане</label><input type="text" name="powerInputCoolingKw" value="<?= e($product['powerInputCoolingKw'] ?? '') ?>"></div>
            <div class="field"><label>Входяща мощност отопление</label><input type="text" name="powerInputHeatingKw" value="<?= e($product['powerInputHeatingKw'] ?? '') ?>"></div>
            <div class="field"><label>Диапазон работа отопление</label><input type="text" name="heatingOperatingRange" value="<?= e($product['heatingOperatingRange'] ?? '') ?>"></div>
            <div class="field"><label>Диапазон работа охлаждане</label><input type="text" name="coolingOperatingRange" value="<?= e($product['coolingOperatingRange'] ?? '') ?>"></div>
        </div>

        <div class="form-section-title">Изображение и източник</div>
        <div class="form-grid">
            <div class="field"><label>Качи изображение</label><input type="file" name="imageFile" accept=".jpg,.jpeg,.png,.webp" data-product-image-file></div>
            <div class="field"><label>Път до изображение</label><input type="text" name="customImagePath" value="<?= e($product['customImagePath'] ?? '') ?>" placeholder="/images/products/..." data-product-image-path></div>
            <div class="field"><label>Източник на снимка</label><input type="text" name="customImageSource" value="<?= e($product['customImageSource'] ?? '') ?>" placeholder="Официален сайт на производителя"></div>
            <div class="field"><label>Заглавие на източника</label><input type="text" name="sourceTitle" value="<?= e($product['sourceTitle'] ?? '') ?>" placeholder="Техническа страница"></div>
            <div class="field field--span-2"><label>Линк към източника</label><input type="text" name="sourceUrl" value="<?= e($product['sourceUrl'] ?? '') ?>" placeholder="https://..."></div>
        </div>

        <div class="image-preview-card" data-product-image-preview<?= empty($existingImage) ? ' hidden' : '' ?>>
            <span class="image-preview-card__label" data-product-image-preview-label><?= !empty($existingImage) ? 'Текущо изображение' : 'Преглед на изображение' ?></span>
            <img src="<?= e($existingImage ?? '') ?>" alt="<?= e($product['modelLabel'] ?? 'Продукт') ?>" data-product-image-preview-img>
            <p class="image-preview-card__hint" data-product-image-preview-hint>
                <?= !empty($existingImage) ? 'Можеш да качиш нов файл или да смениш пътя, за да обновиш прегледа.' : 'Избери файл или въведи път, за да видиш преглед преди запис.' ?>
            </p>
        </div>

        <div class="form-section-title">Бележки и публикуване</div>
        <div class="field">
            <label>Бележки (по една на ред)</label>
            <textarea name="notes" rows="5"><?= e(implode("\n", $product['notes'] ?? [])) ?></textarea>
        </div>
        <div class="button-row button-row--wrap">
            <button class="button button--primary" type="submit">Запиши продукта</button>
            <a class="button" href="/admin/products">Отказ</a>
        </div>
    </form>
</section>
