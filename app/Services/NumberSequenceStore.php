<?php

declare(strict_types=1);

namespace App\Services;

final class NumberSequenceStore
{
    public function __construct(
        private readonly string $filePath = __DIR__ . '/../../resources/data/number_sequences.json'
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

        return array_merge($defaults, $decoded);
    }

    /**
     * @param array<string, mixed> $sequences
     */
    public function save(array $sequences): void
    {
        $normalized = array_merge($this->defaults(), $sequences);

        $json = json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new \RuntimeException('Nummernkreise konnten nicht serialisiert werden.');
        }

        file_put_contents($this->filePath, $json . PHP_EOL);
    }

    public function getNextCustomerNumber(): string
    {
        $sequences = $this->get();
        $customerSeq = $sequences['customer_number'] ?? [];

        $prefix = (string) ($customerSeq['prefix'] ?? 'C');
        $current = (int) ($customerSeq['current'] ?? 1000);
        $padLength = (int) ($customerSeq['pad_length'] ?? 5);

        $nextNumber = $current + 1;
        $sequences['customer_number']['current'] = $nextNumber;

        $this->save($sequences);

        return $prefix . '-' . str_pad((string) $nextNumber, $padLength, '0', STR_PAD_LEFT);
    }

    /**
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        return [
            'customer_number' => [
                'prefix' => 'C',
                'current' => 1570,
                'pad_length' => 5,
            ],
        ];
    }
}
