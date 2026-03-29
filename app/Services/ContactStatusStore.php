<?php

declare(strict_types=1);

namespace App\Services;

final class ContactStatusStore
{
    public function __construct(
        private readonly string $filePath = __DIR__ . '/../../resources/data/contact_statuses.json'
    ) {
    }

    /**
     * @return list<string>
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

        return $this->normalizeStatuses($raw);
    }

    /**
     * @param list<string> $statuses
     */
    public function saveAll(array $statuses): void
    {
        $normalized = $this->normalizeStatuses($statuses);

        $json = json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('Kontakt-Status konnten nicht serialisiert werden.');
        }

        file_put_contents($this->filePath, $json . PHP_EOL);
    }

    /**
     * @param list<mixed> $statuses
     * @return list<string>
     */
    private function normalizeStatuses(array $statuses): array
    {
        $unique = [];
        $result = [];

        foreach ($statuses as $status) {
            $label = trim((string) $status);
            if ($label === '') {
                continue;
            }

            $key = strtolower($label);
            if (isset($unique[$key])) {
                continue;
            }

            $unique[$key] = true;
            $result[] = $label;
        }

        if ($result === []) {
            return ['Kontakt'];
        }

        $hasKontakt = false;
        foreach ($result as $label) {
            if (strtolower($label) === 'kontakt') {
                $hasKontakt = true;
                break;
            }
        }

        if (!$hasKontakt) {
            array_unshift($result, 'Kontakt');
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    private function defaults(): array
    {
        return ['Kontakt', 'Lead', 'Kunde', 'Partner', 'Mitarbeiter'];
    }
}
