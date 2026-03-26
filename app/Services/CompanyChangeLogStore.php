<?php

declare(strict_types=1);

namespace App\Services;

final class CompanyChangeLogStore
{
    public function __construct(
        private readonly string $filePath = __DIR__ . '/../../resources/data/company_change_log.json'
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function latest(int $limit = 25): array
    {
        $entries = $this->readAll();

        if ($limit < 1) {
            return [];
        }

        return array_slice($entries, 0, $limit);
    }

    /**
     * @param array<string, mixed> $entry
     */
    public function append(array $entry): void
    {
        $entries = $this->readAll();
        array_unshift($entries, $entry);

        $json = json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('Aenderungsprotokoll konnte nicht serialisiert werden.');
        }

        file_put_contents($this->filePath, $json . PHP_EOL);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readAll(): array
    {
        if (!is_file($this->filePath)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($this->filePath), true);
        if (!is_array($decoded)) {
            return [];
        }

        $entries = [];
        foreach ($decoded as $entry) {
            if (is_array($entry)) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }
}