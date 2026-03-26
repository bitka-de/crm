<?php

declare(strict_types=1);

namespace App\Services;

final class CompanyProfileStore
{
    public function __construct(
        private readonly string $filePath = __DIR__ . '/../../resources/data/company_profile.json'
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        $defaults = $this->defaults();

        if (!is_file($this->filePath)) {
            return $defaults;
        }

        $decoded = json_decode((string) file_get_contents($this->filePath), true);

        if (!is_array($decoded)) {
            return $defaults;
        }

        $merged = array_merge($defaults, $decoded);

        if (!is_array($merged['extra_fields'] ?? null)) {
            $merged['extra_fields'] = [];
        }

        return $merged;
    }

    /**
     * @param array<string, mixed> $profile
     */
    public function save(array $profile): void
    {
        $normalized = array_merge($this->defaults(), $profile);

        if (!is_array($normalized['extra_fields'] ?? null)) {
            $normalized['extra_fields'] = [];
        }

        $json = json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new \RuntimeException('Profil konnte nicht serialisiert werden.');
        }

        file_put_contents($this->filePath, $json . PHP_EOL);
    }

    /**
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        return [
            'company_name' => '',
            'legal_form' => '',
            'owner_name' => '',
            'managing_director' => '',
            'street' => '',
            'zip_code' => '',
            'city' => '',
            'country' => '',
            'email' => '',
            'phone' => '',
            'website' => '',
            'vat_id' => '',
            'tax_number' => '',
            'registration_number' => '',
            'registration_court' => '',
            'share_capital_eur' => '',
            'founded_on' => '',
            'extra_fields' => [],
        ];
    }
}
