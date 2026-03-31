<?php

declare(strict_types=1);

namespace App\Services;

final class DocumentStore
{
    public function __construct(
        private readonly string $filePath = __DIR__ . '/../../resources/data/documents.json'
    ) {
    }

    /**
     * @return array{offers: list<array<string, mixed>>, invoices: list<array<string, mixed>>, reminders: list<array<string, mixed>>}
     */
    public function getAll(): array
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

        $merged['offers'] = $this->normalizeList($merged['offers'] ?? []);
        $merged['invoices'] = $this->normalizeList($merged['invoices'] ?? []);
        $merged['reminders'] = $this->normalizeList($merged['reminders'] ?? []);

        return $merged;
    }

    /** @param array<string, mixed> $entry */
    public function addOffer(array $entry): void
    {
        $data = $this->getAll();
        array_unshift($data['offers'], $this->normalizeEntry($entry));
        $this->saveAll($data);
    }

    /** @param array<string, mixed> $entry */
    public function addInvoice(array $entry): void
    {
        $data = $this->getAll();
        array_unshift($data['invoices'], $this->normalizeEntry($entry));
        $this->saveAll($data);
    }

    /** @param array<string, mixed> $entry */
    public function addReminder(array $entry): void
    {
        $data = $this->getAll();
        array_unshift($data['reminders'], $this->normalizeEntry($entry));
        $this->saveAll($data);
    }

    public function deleteEntry(string $type, string $id): void
    {
        $allowed = ['offers', 'invoices', 'reminders'];
        if (!in_array($type, $allowed, true) || $id === '') {
            return;
        }

        $data = $this->getAll();
        $data[$type] = array_values(array_filter(
            $data[$type],
            static fn (array $entry): bool => (string) ($entry['id'] ?? '') !== $id
        ));

        $this->saveAll($data);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findEntry(string $type, string $id): ?array
    {
        $allowed = ['offers', 'invoices', 'reminders'];
        if (!in_array($type, $allowed, true) || $id === '') {
            return null;
        }

        $data = $this->getAll();
        foreach ($data[$type] as $entry) {
            if ((string) ($entry['id'] ?? '') === $id) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateInvoice(string $id, array $data): void
    {
        $all = $this->getAll();
        foreach ($all['invoices'] as $index => $entry) {
            if ((string) ($entry['id'] ?? '') !== $id) {
                continue;
            }

            $all['invoices'][$index] = $this->normalizeEntry($data);
            $this->saveAll($all);
            return;
        }
    }

    /**
     * @param array{offers: list<array<string, mixed>>, invoices: list<array<string, mixed>>, reminders: list<array<string, mixed>>} $data
     */
    private function saveAll(array $data): void
    {
        $payload = [
            'offers' => $this->normalizeList($data['offers'] ?? []),
            'invoices' => $this->normalizeList($data['invoices'] ?? []),
            'reminders' => $this->normalizeList($data['reminders'] ?? []),
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('Dokumente konnten nicht serialisiert werden.');
        }

        file_put_contents($this->filePath, $json . PHP_EOL);
    }

    /**
     * @param mixed $entries
     * @return list<array<string, mixed>>
     */
    private function normalizeList(mixed $entries): array
    {
        if (!is_array($entries)) {
            return [];
        }

        $result = [];
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $result[] = $this->normalizeEntry($entry);
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $entry
     * @return array<string, mixed>
     */
    private function normalizeEntry(array $entry): array
    {
        $normalized = [];
        foreach ($entry as $key => $value) {
            $normalized[(string) $key] = $this->normalizeValue($value);
        }

        return $normalized;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                if (is_int($key)) {
                    $normalized[] = $this->normalizeValue($item);
                    continue;
                }

                $normalized[(string) $key] = $this->normalizeValue($item);
            }

            return $normalized;
        }

        if (is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        return trim((string) $value);
    }

    /**
     * @return array{offers: list<array<string, mixed>>, invoices: list<array<string, mixed>>, reminders: list<array<string, mixed>>}
     */
    private function defaults(): array
    {
        return [
            'offers' => [],
            'invoices' => [],
            'reminders' => [],
        ];
    }
}
