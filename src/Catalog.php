<?php

declare(strict_types=1);

final class Catalog
{
    private array $productImageMap = [
        'Gree::Pular' => ['/images/products/gree-pular.webp', 'https://gree-bulgaria.com/pular/'],
        'Gree::Clivia' => ['/images/products/gree-clivia.jpg', 'https://gree-bulgaria.com/clivia-invertorna-mono-split-sistema-za-stenen-montazh-2/'],
        'Crystal::Emerald' => ['/images/products/crystal-emerald.jpg', 'https://crystalbgr.com/product/invertoren-klimatik-crystal-emerald-25h-uw-wi-fi/'],
        'Crystal::Quartz' => ['/images/products/crystal-quartz.jpg', 'https://crystalbgr.com/en/product/invertoren-klimatik-crystal-quartz-24h-ka-visokostene/'],
        'Daikin::Perfera' => ['/images/products/daikin-perfera.jpg', 'https://www.daikin.eu/en_us/product-group/air-to-air-heat-pumps/perfera.html'],
        'Daikin::Ururu Sarara' => ['/images/products/daikin-ururu-sarara.jpg', 'https://www.daikin.eu/en_us/product-group/air-to-air-heat-pumps/ururu-sarara.html'],
        'Fujitsu Airstage::KJCA' => ['/images/products/fujitsu-airstage-kjca.png', 'https://www.generalww.com/shared/pdf-feu-ctlg-kj-series-2025-01.pdf'],
        'Mitsubishi Heavy::Standard' => ['/images/products/mitsubishi-premium.jpg', 'https://www.mhi.com/'],
        'Mitsubishi Heavy::Premium' => ['/images/products/mitsubishi-premium.jpg', 'https://www.mhi.com/'],
        'Mitsubishi Heavy::Diamant' => ['/images/products/mitsubishi-diamond.jpg', 'https://www.mhi.com/'],
        'LG::Therma V' => ['/images/products/lg-therma-v.jpg', 'https://www.lg.com/global/business/hvac/residential-solutions/air-to-water-heat-pumps/therma-v-split'],
        'Crystal::Lava' => ['/images/products/crystal-lava.jpg', 'https://crystalbgr.com/en/product/termopompa-vazduh-voda-monoblok-crystal-lava-lava-09am/'],
        'Crystal::Onyx' => ['/images/products/crystal-onyx.jpg', 'https://crystalbgr.com/product/invertorna-termopompa-vazduh-voda-crystal-onyx-pro-12s/'],
    ];

    private array $brandLogos = [
        'Gree' => '/images/brands/gree-logo.png',
        'Crystal' => '/images/brands/crystal-logo.svg',
        'Daikin' => '/images/brands/daikin-logo.png',
        'LG' => '/images/brands/lg-logo.svg',
        'Fujitsu Airstage' => '/images/brands/fujitsu-airstage-logo.svg',
        'Mitsubishi Heavy' => '/images/brands/mitsubishi-heavy-logo.svg',
    ];

    public function __construct(
        private readonly DataStore $dataStore,
    ) {
    }

    public function getCompany(): array
    {
        return $this->dataStore->getCompanyProfile();
    }

    public function getSettings(): array
    {
        return $this->dataStore->getAdminSettings();
    }

    public function getPromotions(bool $onlyActive = false): array
    {
        $items = $this->dataStore->getPromotions()['items'] ?? [];
        $items = array_values(array_filter($items, static fn ($item) => is_array($item)));

        usort($items, static fn (array $a, array $b) => ($a['sortOrder'] ?? 99) <=> ($b['sortOrder'] ?? 99));

        if ($onlyActive) {
            $items = array_values(array_filter($items, static fn (array $item) => (bool) ($item['isActive'] ?? false)));
        }

        return $items;
    }

    public function getProductsByCategory(string $category): array
    {
        $seed = $this->dataStore->getProductSeed();
        $key = $category === 'heatPumps' ? 'heatPumps' : 'airConditioners';

        return $this->flattenProducts($seed[$key] ?? [], $key);
    }

    public function getProductBySlug(string $category, string $slug): ?array
    {
        foreach ($this->getProductsByCategory($category) as $product) {
            if ($product['slug'] === $slug) {
                return $product;
            }
        }

        return null;
    }

    public function findRawProduct(string $category, string $slug): ?array
    {
        $seed = $this->dataStore->getProductSeed();
        $key = $category === 'heatPumps' ? 'heatPumps' : 'airConditioners';

        foreach (($seed[$key] ?? []) as $series) {
            if (!is_array($series)) {
                continue;
            }

            foreach (($series['models'] ?? []) as $model) {
                $candidateSlug = slugify(($series['brand'] ?? '') . '-' . ($series['series'] ?? '') . '-' . ($model['modelLabel'] ?? ''));
                if ($candidateSlug === $slug) {
                    return [
                        'brand' => (string) ($series['brand'] ?? ''),
                        'series' => (string) ($series['series'] ?? ''),
                        'model' => $model,
                    ];
                }
            }
        }

        return null;
    }

    public function getBrandShowcase(): array
    {
        return [
            ['name' => 'Gree', 'logoPath' => '/images/brands/gree-logo.png', 'note' => 'Инверторни решения за дома'],
            ['name' => 'Crystal', 'logoPath' => '/images/brands/crystal-logo.svg', 'note' => 'Климатици и термопомпи'],
            ['name' => 'Daikin', 'logoPath' => '/images/brands/daikin-logo.png', 'note' => 'Премиум климатизация'],
            ['name' => 'LG', 'logoPath' => '/images/brands/lg-logo.svg', 'note' => 'Въздух-вода системи'],
            ['name' => 'Fujitsu Airstage', 'logoPath' => '/images/brands/fujitsu-airstage-logo.svg', 'note' => 'KJ серия с отопление до -20 C'],
            ['name' => 'Mitsubishi Heavy', 'logoPath' => '/images/brands/mitsubishi-heavy-logo.svg', 'note' => 'Висок клас стенни модели'],
        ];
    }

    public function getOfficialBrandLinks(): array
    {
        return [
            ['name' => 'Gree', 'url' => 'https://global.gree.com/'],
            ['name' => 'Crystal', 'url' => 'https://crystalbgr.com/en/'],
            ['name' => 'Daikin', 'url' => 'https://www.daikin.com/'],
            ['name' => 'Fujitsu Airstage', 'url' => 'https://www.fujitsu-general.com/eu/products/split/wall/'],
            ['name' => 'Mitsubishi Heavy', 'url' => 'https://www.mhi.com/'],
            ['name' => 'LG', 'url' => 'https://www.lg.com/global/business/'],
        ];
    }

    public function getBrandOptions(string $category): array
    {
        $products = $this->getProductsByCategory($category);
        $brands = array_unique(array_map(static fn (array $product) => $product['brand'], $products));
        sort($brands);

        return $brands;
    }

    private function flattenProducts(array $seriesList, string $category): array
    {
        $products = [];

        foreach ($seriesList as $series) {
            if (!is_array($series)) {
                continue;
            }

            $brand = (string) ($series['brand'] ?? '');
            $seriesName = (string) ($series['series'] ?? '');

            foreach (($series['models'] ?? []) as $model) {
                if (!is_array($model)) {
                    continue;
                }

                $slug = slugify($brand . '-' . $seriesName . '-' . (string) ($model['modelLabel'] ?? ''));
                [$imagePath, $imageSource] = $this->resolveImage($brand, $seriesName, $model);
                $priceBgn = isset($model['priceBgn']) && is_numeric($model['priceBgn']) ? (float) $model['priceBgn'] : $this->inferStarterPrice($brand, $seriesName, $category, $model);
                $technology = (string) ($model['technology'] ?? 'pending');

                $products[] = [
                    'id' => $slug,
                    'slug' => $slug,
                    'category' => $category,
                    'brand' => $brand,
                    'series' => $seriesName,
                    'model' => (string) ($model['modelLabel'] ?? ''),
                    'title' => trim($brand . ' ' . (string) ($model['modelLabel'] ?? '')),
                    'description' => $model['description'] ?? null,
                    'priceBgn' => $priceBgn,
                    'priceEur' => convert_bgn_to_eur($priceBgn),
                    'btu' => isset($model['btu']) && is_numeric($model['btu']) ? (int) $model['btu'] : null,
                    'powerKw' => isset($model['powerKw']) && is_numeric($model['powerKw']) ? (float) $model['powerKw'] : null,
                    'technology' => $technology,
                    'type' => $model['type'] ?? null,
                    'typeLabel' => $this->typeLabel($category, $technology, $model['type'] ?? null),
                    'status' => (string) ($model['status'] ?? 'draft'),
                    'energyCooling' => (string) ($model['energyCooling'] ?? ($category === 'airConditioners' ? 'По каталог' : '—')),
                    'energyHeating' => (string) ($model['energyHeating'] ?? 'По каталог'),
                    'imagePath' => $imagePath,
                    'imageSource' => $imageSource,
                    'brandLogo' => $this->brandLogos[$brand] ?? null,
                    'officialModelCode' => $model['officialModelCode'] ?? null,
                    'indoorUnit' => $model['indoorUnit'] ?? null,
                    'outdoorUnit' => $model['outdoorUnit'] ?? null,
                    'nominalCoolingKw' => isset($model['nominalCoolingKw']) && is_numeric($model['nominalCoolingKw']) ? (float) $model['nominalCoolingKw'] : null,
                    'nominalHeatingKw' => isset($model['nominalHeatingKw']) && is_numeric($model['nominalHeatingKw']) ? (float) $model['nominalHeatingKw'] : null,
                    'coolingRangeKw' => $model['coolingRangeKw'] ?? null,
                    'heatingRangeKw' => $model['heatingRangeKw'] ?? null,
                    'powerInputCoolingKw' => $model['powerInputCoolingKw'] ?? null,
                    'powerInputHeatingKw' => $model['powerInputHeatingKw'] ?? null,
                    'seer' => isset($model['seer']) && is_numeric($model['seer']) ? (float) $model['seer'] : null,
                    'scop' => isset($model['scop']) && is_numeric($model['scop']) ? (float) $model['scop'] : null,
                    'coverageM2' => $model['coverageM2'] ?? null,
                    'indoorNoiseDb' => $model['indoorNoiseDb'] ?? null,
                    'outdoorNoiseDb' => $model['outdoorNoiseDb'] ?? null,
                    'indoorDimensionsMm' => $model['indoorDimensionsMm'] ?? null,
                    'outdoorDimensionsMm' => $model['outdoorDimensionsMm'] ?? null,
                    'indoorWeightKg' => isset($model['indoorWeightKg']) && is_numeric($model['indoorWeightKg']) ? (float) $model['indoorWeightKg'] : null,
                    'outdoorWeightKg' => isset($model['outdoorWeightKg']) && is_numeric($model['outdoorWeightKg']) ? (float) $model['outdoorWeightKg'] : null,
                    'refrigerant' => $model['refrigerant'] ?? null,
                    'wifi' => (bool) ($model['wifi'] ?? false),
                    'heatingOperatingRange' => $model['heatingOperatingRange'] ?? null,
                    'coolingOperatingRange' => $model['coolingOperatingRange'] ?? null,
                    'sourceTitle' => $model['sourceTitle'] ?? null,
                    'sourceUrl' => $model['sourceUrl'] ?? null,
                    'notes' => array_values(array_filter($model['notes'] ?? [], static fn ($value) => is_string($value) && $value !== '')),
                    'features' => $this->buildFeatures($category, $brand, $seriesName, $model),
                ];
            }
        }

        return $products;
    }

    private function resolveImage(string $brand, string $seriesName, array $model): array
    {
        if (!empty($model['customImagePath'])) {
            return [(string) $model['customImagePath'], $model['customImageSource'] ?? null];
        }

        return $this->productImageMap[$brand . '::' . $seriesName] ?? [null, null];
    }

    private function typeLabel(string $category, string $technology, ?string $type): string
    {
        if ($category === 'heatPumps') {
            return match ($type) {
                'split' => 'Split термопомпа',
                'monoblock' => 'Monoblock термопомпа',
                default => 'Термопомпа',
            };
        }

        return match ($technology) {
            'hyperinverter' => 'Хиперинвертор',
            'inverter' => 'Инвертор',
            default => 'Климатик',
        };
    }

    private function buildFeatures(string $category, string $brand, string $seriesName, array $model): array
    {
        $features = [];

        if ($category === 'airConditioners' && !empty($model['btu'])) {
            $features[] = (int) $model['btu'] . ' BTU клас';
        }

        if ($category === 'heatPumps' && !empty($model['powerKw'])) {
            $features[] = number_format((float) $model['powerKw'], 0, ',', ' ') . ' kW мощност';
        }

        if (!empty($model['energyCooling'])) {
            $features[] = 'Охлаждане ' . $model['energyCooling'];
        }

        if (!empty($model['energyHeating'])) {
            $features[] = 'Отопление ' . $model['energyHeating'];
        }

        if (!empty($model['wifi'])) {
            $features[] = 'Вграден Wi-Fi';
        }

        if (!empty($model['heatingOperatingRange'])) {
            $features[] = 'Диапазон отопление: ' . $model['heatingOperatingRange'];
        }

        if (!empty($model['coverageM2'])) {
            $features[] = 'Препоръчително покритие: ' . $model['coverageM2'];
        }

        foreach (($model['notes'] ?? []) as $note) {
            if (is_string($note) && $note !== '') {
                $features[] = $note;
            }
        }

        $features[] = 'Стандартен монтаж до 3 м тръбен път';

        return array_values(array_unique($features));
    }

    private function inferStarterPrice(string $brand, string $seriesName, string $category, array $model): ?float
    {
        $btu = isset($model['btu']) && is_numeric($model['btu']) ? (int) $model['btu'] : 0;

        $map = [
            'Gree::Pular::9000' => 1299.0,
            'Gree::Pular::12000' => 1499.0,
            'Gree::Clivia::9000' => 1232.17,
            'Gree::Clivia::12000' => 1279.11,
            'Gree::Clivia::18000' => 1877.60,
            'Crystal::Emerald::9000' => 1399.0,
            'Crystal::Emerald::12000' => 1599.0,
            'Crystal::Quartz::9000' => 1499.0,
            'Crystal::Quartz::12000' => 1699.0,
            'Crystal::Quartz::18000' => 2399.0,
            'Daikin::Perfera::9000' => 2899.0,
            'Daikin::Perfera::12000' => 3299.0,
            'Daikin::Perfera::18000' => 4299.0,
            'Daikin::Ururu Sarara::9000' => 5599.0,
            'Daikin::Ururu Sarara::12000' => 6199.0,
            'Daikin::Ururu Sarara::18000' => 7299.0,
            'Fujitsu Airstage::KJCA::7000' => 2403.72,
            'Fujitsu Airstage::KJCA::9000' => 2501.51,
            'Fujitsu Airstage::KJCA::12000' => 2834.0,
            'Fujitsu Airstage::KJCA::14000' => 3186.05,
            'Mitsubishi Heavy::Standard::9000' => 1699.0,
            'Mitsubishi Heavy::Standard::12000' => 1899.0,
            'Mitsubishi Heavy::Standard::18000' => 2599.0,
            'Mitsubishi Heavy::Premium::9000' => 2599.0,
            'Mitsubishi Heavy::Premium::12000' => 2899.0,
            'Mitsubishi Heavy::Premium::18000' => 3699.0,
            'Mitsubishi Heavy::Diamant::9000' => 3099.0,
            'Mitsubishi Heavy::Diamant::12000' => 3399.0,
            'Mitsubishi Heavy::Diamant::18000' => 4399.0,
        ];

        $lookupKey = $brand . '::' . $seriesName . '::' . $btu;
        if (isset($map[$lookupKey])) {
            return $map[$lookupKey];
        }

        if ($category === 'heatPumps') {
            return match ($seriesName) {
                'Therma V' => 13999.0,
                'Lava' => 10499.0,
                'Onyx' => 15499.0,
                default => null,
            };
        }

        return null;
    }
}
