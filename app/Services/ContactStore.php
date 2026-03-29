<?php

declare(strict_types=1);

namespace App\Services;

final class ContactStore
{
    public function __construct(
        private readonly string $filePath = __DIR__ . '/../../resources/data/contacts.json'
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAll(): array
    {
        if (!is_file($this->filePath)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($this->filePath), true);
        if (!is_array($decoded)) {
            return [];
        }

        $contacts = [];
        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }

            $contacts[] = $this->normalizeContact($item);
        }

        return $contacts;
    }

    /**
     * @param list<array<string, mixed>> $contacts
     */
    public function saveAll(array $contacts): void
    {
        $normalized = [];
        foreach ($contacts as $contact) {
            if (!is_array($contact)) {
                continue;
            }

            $normalized[] = $this->normalizeContact($contact);
        }

        $json = json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('Kontakte konnten nicht serialisiert werden.');
        }

        file_put_contents($this->filePath, $json . PHP_EOL);
    }

    /**
     * @param array<string, mixed> $contact
     */
    public function upsert(array $contact): void
    {
        $normalized = $this->normalizeContact($contact);
        $contacts = $this->getAll();
        $updated = false;

        foreach ($contacts as $index => $existing) {
            if ((string) ($existing['id'] ?? '') !== (string) ($normalized['id'] ?? '')) {
                continue;
            }

            $contacts[$index] = $normalized;
            $updated = true;
            break;
        }

        if (!$updated) {
            $contacts[] = $normalized;
        }

        $this->saveAll($contacts);
    }

    public function deleteById(string $id): void
    {
        if ($id === '') {
            return;
        }

        $remaining = [];
        foreach ($this->getAll() as $contact) {
            if ((string) ($contact['id'] ?? '') === $id) {
                continue;
            }

            $remaining[] = $contact;
        }

        $this->saveAll($remaining);
    }

    /**
     * @param array<string, mixed> $contact
     * @return array<string, mixed>
     */
    private function normalizeContact(array $contact): array
    {
        $defaults = [
            'id' => '',
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'company' => '',
            'position' => '',
            'status' => 'Kontakt',
            'extra_fields' => [],
        ];

        $merged = array_merge($defaults, $contact);

        if (!is_array($merged['extra_fields'] ?? null)) {
            $merged['extra_fields'] = [];
        }

        $extraFields = [];
        foreach ($merged['extra_fields'] as $key => $field) {
            if (is_array($field) && isset($field['type'], $field['value'])) {
                $extraFields[(string) $key] = [
                    'type' => (string) $field['type'],
                    'value' => $field['value'],
                ];
                continue;
            }

            $extraFields[(string) $key] = [
                'type' => 'text',
                'value' => is_scalar($field) || $field === null ? (string) $field : '',
            ];
        }

        $merged['id'] = trim((string) $merged['id']);
        $merged['first_name'] = trim((string) $merged['first_name']);
        $merged['last_name'] = trim((string) $merged['last_name']);
        $merged['email'] = trim((string) $merged['email']);
        $merged['phone'] = trim((string) $merged['phone']);
        $merged['company'] = trim((string) $merged['company']);
        $merged['position'] = trim((string) $merged['position']);
        $merged['status'] = trim((string) $merged['status']);
        $merged['extra_fields'] = $extraFields;

        return $merged;
    }
}
