<?php

declare(strict_types=1);

namespace App\Services;

final class ContactCompanyStore
{
    public function __construct(
        private readonly string $filePath = __DIR__ . '/../../resources/data/contact_companies.json'
    ) {
    }

    /**
     * @return list<array<string, string>>
     */
    public function getAll(): array
    {
        $raw = $this->defaults();

        if (is_file($this->filePath)) {
            $decoded = json_decode((string) file_get_contents($this->filePath), true);
            if (is_array($decoded)) {
                $raw = $decoded;
            }
        }

        return $this->normalizeCompanies($raw);
    }

    /**
     * @param list<array<string, mixed>> $companies
     */
    public function saveAll(array $companies): void
    {
        $normalized = $this->normalizeCompanies($companies);

        $json = json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('Kontakt-Firmen konnten nicht serialisiert werden.');
        }

        file_put_contents($this->filePath, $json . PHP_EOL);
    }

    /**
     * @param list<mixed> $companies
     * @return list<array<string, string>>
     */
    private function normalizeCompanies(array $companies): array
    {
        $unique = [];
        $result = [];

        foreach ($companies as $company) {
            $record = $this->normalizeCompanyRecord($company);
            $label = $record['company_name'];

            if ($label === '') {
                continue;
            }

            $key = strtolower($label);
            if (isset($unique[$key])) {
                continue;
            }

            $unique[$key] = true;
            $result[] = $record;
        }

        if ($result === []) {
            return $this->defaults();
        }

        return $result;
    }

    /**
     * @param mixed $raw
     * @return array<string, string>
     */
    private function normalizeCompanyRecord(mixed $raw): array
    {
        // Rueckwaertskompatibel: alte Struktur als reiner String.
        if (is_string($raw)) {
            $raw = [
                'company_name' => $raw,
            ];
        }

        if (!is_array($raw)) {
            $raw = [];
        }

        $defaults = [
            'company_name' => '',
            'legal_form' => '',
            'owner_name' => '',
            'managing_director' => '',
            'founded_on' => '',
            'street' => '',
            'zip_code' => '',
            'city' => '',
            'country' => 'Deutschland',
            'email' => '',
            'phone' => '',
            'website' => '',
            'vat_id' => '',
            'tax_number' => '',
            'registration_number' => '',
            'registration_court' => '',
            'share_capital_eur' => '',
            'extra_fields' => [],
        ];

        $merged = array_merge($defaults, $raw);

        foreach ($merged as $key => $value) {
            $merged[(string) $key] = is_array($value) ? $value : trim((string) $value);
        }

        return $merged;
    }

    /**
     * @return list<array<string, string>>
     */
    private function defaults(): array
    {
        return [
            [
                'company_name' => 'Musterfirma GmbH',
                'legal_form' => 'GmbH',
                'owner_name' => '',
                'managing_director' => 'Max Mustermann',
                'founded_on' => '',
                'street' => 'Musterstrasse 10',
                'zip_code' => '20095',
                'city' => 'Hamburg',
                'country' => 'Deutschland',
                'email' => 'info@musterfirma.de',
                'phone' => '+49 40 123456-0',
                'website' => 'https://musterfirma.de',
                'vat_id' => 'DE123456789',
                'tax_number' => '12/345/67890',
                'registration_number' => 'HRB 123456',
                'registration_court' => 'Amtsgericht Hamburg',
                'share_capital_eur' => '25000',
                'extra_fields' => [],
            ],
        ];
    }
}
