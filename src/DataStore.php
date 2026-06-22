<?php

declare(strict_types=1);

final class DataStore
{
    public function __construct(
        private readonly string $dataDirectory,
    ) {
    }

    public function getCompanyProfile(): array
    {
        return $this->readJson('company-profile.json');
    }

    public function saveCompanyProfile(array $payload): void
    {
        $this->writeJson('company-profile.json', $payload);
    }

    public function getAdminSettings(): array
    {
        return $this->readJson('admin-settings.json');
    }

    public function saveAdminSettings(array $payload): void
    {
        $this->writeJson('admin-settings.json', $payload);
    }

    public function getPromotions(): array
    {
        return $this->readJson('promotions.json');
    }

    public function savePromotions(array $payload): void
    {
        $this->writeJson('promotions.json', $payload);
    }

    public function getProductSeed(): array
    {
        return $this->readJson('seed-products.json');
    }

    public function saveProductSeed(array $payload): void
    {
        $this->writeJson('seed-products.json', $payload);
    }

    private function readJson(string $fileName): array
    {
        $path = $this->dataDirectory . DIRECTORY_SEPARATOR . $fileName;
        $raw = file_get_contents($path);

        if ($raw === false) {
            throw new RuntimeException('Неуспешно четене на ' . $path);
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Невалиден JSON в ' . $path);
        }

        return $decoded;
    }

    private function writeJson(string $fileName, array $payload): void
    {
        if (!is_dir($this->dataDirectory)) {
            mkdir($this->dataDirectory, 0775, true);
        }

        $path = $this->dataDirectory . DIRECTORY_SEPARATOR . $fileName;
        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($encoded === false) {
            throw new RuntimeException('Неуспешно сериализиране на ' . $fileName);
        }

        file_put_contents($path, $encoded . PHP_EOL, LOCK_EX);
    }
}
