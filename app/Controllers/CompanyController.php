<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Session;
use App\Core\View;
use App\Services\CompanyChangeLogStore;
use App\Services\CompanyProfileStore;

final class CompanyController
{
    private const LEGAL_FORMS = [
        'Freelancer',
        'Einzelunternehmen',
        'GbR',
        'UG (haftungsbeschraenkt)',
        'GmbH',
    ];

    private CompanyProfileStore $store;
    private CompanyChangeLogStore $changeLogStore;

    public function __construct()
    {
        $this->store = new CompanyProfileStore();
        $this->changeLogStore = new CompanyChangeLogStore();
    }

    public function index(): string
    {
        $success = Session::get('company_success');
        Session::remove('company_success');

        $error = Session::get('company_error');
        Session::remove('company_error');

        return (new View())->render('company/index', [
            'title' => 'Unternehmensdaten',
            'profile' => $this->store->get(),
            'changeLog' => $this->changeLogStore->latest(25),
            'success' => is_string($success) ? $success : null,
            'error' => is_string($error) ? $error : null,
        ]);
    }

    public function update(): void
    {
        $extraFields = $this->buildExtraFields(
            $_POST['extra_field_key'] ?? [],
            $_POST['extra_field_value'] ?? [],
            $_POST['extra_field_type'] ?? []
        );

        if ($extraFields === null) {
            Session::set('company_error', 'Zusatzfelder enthalten doppelte oder ungueltige Schluessel.');
            http_response_code(302);
            header('Location: /company');
            exit;
        }

        $profile = [
            'company_name' => trim((string) ($_POST['company_name'] ?? '')),
            'legal_form' => trim((string) ($_POST['legal_form'] ?? '')),
            'owner_name' => trim((string) ($_POST['owner_name'] ?? '')),
            'managing_director' => trim((string) ($_POST['managing_director'] ?? '')),
            'street' => trim((string) ($_POST['street'] ?? '')),
            'zip_code' => trim((string) ($_POST['zip_code'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'country' => trim((string) ($_POST['country'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'website' => trim((string) ($_POST['website'] ?? '')),
            'bank_name' => trim((string) ($_POST['bank_name'] ?? '')),
            'account_holder' => trim((string) ($_POST['account_holder'] ?? '')),
            'iban' => trim((string) ($_POST['iban'] ?? '')),
            'bic' => trim((string) ($_POST['bic'] ?? '')),
            'vat_id' => trim((string) ($_POST['vat_id'] ?? '')),
            'tax_number' => trim((string) ($_POST['tax_number'] ?? '')),
            'registration_number' => trim((string) ($_POST['registration_number'] ?? '')),
            'registration_court' => trim((string) ($_POST['registration_court'] ?? '')),
            'share_capital_eur' => trim((string) ($_POST['share_capital_eur'] ?? '')),
            'founded_on' => trim((string) ($_POST['founded_on'] ?? '')),
            'extra_fields' => $extraFields,
        ];

        $profile = $this->normalizeByLegalForm($profile);

        $validationError = $this->validateByLegalForm($profile);
        if ($validationError !== null) {
            Session::set('company_error', $validationError);
            http_response_code(302);
            header('Location: /company');
            exit;
        }

        $existingProfile = $this->store->get();
        $changes = $this->buildChangeEntries($existingProfile, $profile);

        $this->store->save($profile);

        if ($changes !== []) {
            $user = Auth::user();
            $this->changeLogStore->append([
                'changed_at' => (new \DateTimeImmutable())->format(DATE_ATOM),
                'changed_by' => is_string($user) && $user !== '' ? $user : 'system',
                'changes' => $changes,
            ]);
        }

        Session::set('company_success', 'Unternehmensdaten wurden gespeichert.');
        http_response_code(302);
        header('Location: /company');
        exit;
    }

    /**
     * @param mixed $rawKeys
     * @param mixed $rawValues
     * @param mixed $rawTypes
     * @return array<string, array{type: string, value: string|int|float|bool}>|null
     */
    private function buildExtraFields(mixed $rawKeys, mixed $rawValues, mixed $rawTypes): ?array
    {
        if (!is_array($rawKeys) || !is_array($rawValues) || !is_array($rawTypes)) {
            return null;
        }

        $allowedTypes = ['text', 'number', 'boolean', 'date'];
        $extraFields = [];

        $count = max(count($rawKeys), count($rawValues), count($rawTypes));

        for ($index = 0; $index < $count; $index++) {
            $key = trim((string) ($rawKeys[$index] ?? ''));
            $value = trim((string) ($rawValues[$index] ?? ''));
            $type = trim((string) ($rawTypes[$index] ?? 'text'));

            if ($key === '' && $value === '') {
                continue;
            }

            if ($key === '' || isset($extraFields[$key])) {
                return null;
            }

            if (!preg_match('/^[A-Za-z0-9_\-]+$/', $key)) {
                return null;
            }

            if (!in_array($type, $allowedTypes, true)) {
                $type = 'text';
            }

            $extraFields[$key] = [
                'type' => $type,
                'value' => $this->castExtraFieldValue($value, $type),
            ];
        }

        return $extraFields;
    }

    private function castExtraFieldValue(string $value, string $type): string|int|float|bool
    {
        if ($type === 'number') {
            if (is_numeric($value)) {
                return str_contains($value, '.') ? (float) $value : (int) $value;
            }

            return $value;
        }

        if ($type === 'boolean') {
            return in_array(strtolower($value), ['1', 'true', 'ja', 'yes'], true);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $profile
     * @return array<string, mixed>
     */
    private function normalizeByLegalForm(array $profile): array
    {
        $legalForm = $profile['legal_form'];

        if (!is_string($legalForm) || !in_array($legalForm, self::LEGAL_FORMS, true)) {
            $profile['legal_form'] = 'Einzelunternehmen';
            $legalForm = 'Einzelunternehmen';
        }

        if ($legalForm === 'Freelancer') {
            $profile['registration_number'] = '';
            $profile['registration_court'] = '';
            $profile['share_capital_eur'] = '';
            $profile['managing_director'] = '';
        }

        if ($legalForm === 'Einzelunternehmen') {
            $profile['share_capital_eur'] = '';
            $profile['managing_director'] = '';
        }

        if ($legalForm === 'GbR') {
            $profile['share_capital_eur'] = '';
            $profile['managing_director'] = '';
        }

        return $profile;
    }

    /**
     * @param array<string, mixed> $profile
     */
    private function validateByLegalForm(array $profile): ?string
    {
        $legalForm = (string) ($profile['legal_form'] ?? '');

        if ((string) ($profile['company_name'] ?? '') === '') {
            return 'Bitte einen Unternehmensnamen eintragen.';
        }

        if ((string) ($profile['tax_number'] ?? '') === '') {
            return 'Bitte eine Steuernummer eintragen.';
        }

        if ($legalForm === 'Einzelunternehmen' && (string) ($profile['owner_name'] ?? '') === '') {
            return 'Beim Einzelunternehmen bitte den Inhaber angeben.';
        }

        if ($legalForm === 'GbR' && (string) ($profile['owner_name'] ?? '') === '') {
            return 'Bei einer GbR bitte die Gesellschafter angeben.';
        }

        if (
            in_array($legalForm, ['UG (haftungsbeschraenkt)', 'GmbH'], true)
            && (
                (string) ($profile['managing_director'] ?? '') === ''
                || (string) ($profile['registration_number'] ?? '') === ''
                || (string) ($profile['registration_court'] ?? '') === ''
                || (string) ($profile['share_capital_eur'] ?? '') === ''
            )
        ) {
            return 'Bei UG und GmbH sind Geschaeftsfuehrer, Handelsregister und Stammkapital Pflichtfelder.';
        }

        return null;
    }

    /**
     * @param array<string, mixed> $previousProfile
     * @param array<string, mixed> $newProfile
     * @return array<int, array{field: string, from: string, to: string}>
     */
    private function buildChangeEntries(array $previousProfile, array $newProfile): array
    {
        $before = $this->flattenProfileForDiff($previousProfile);
        $after = $this->flattenProfileForDiff($newProfile);

        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        sort($keys);

        $changes = [];
        foreach ($keys as $key) {
            $from = $before[$key] ?? '';
            $to = $after[$key] ?? '';

            if ($from === $to) {
                continue;
            }

            $changes[] = [
                'field' => $this->humanFieldName($key),
                'from' => $from,
                'to' => $to,
            ];
        }

        return $changes;
    }

    /**
     * @param array<string, mixed> $profile
     * @return array<string, string>
     */
    private function flattenProfileForDiff(array $profile): array
    {
        $result = [];

        foreach ($profile as $key => $value) {
            if ($key === 'extra_fields') {
                continue;
            }

            $result[(string) $key] = $this->stringifyForDiff($value);
        }

        $extraFields = $profile['extra_fields'] ?? [];
        if (is_array($extraFields)) {
            foreach ($extraFields as $extraKey => $extraValue) {
                $normalizedKey = 'extra_fields.' . (string) $extraKey;

                if (is_array($extraValue)) {
                    $type = $this->stringifyForDiff($extraValue['type'] ?? 'text');
                    $value = $this->stringifyForDiff($extraValue['value'] ?? '');
                    $result[$normalizedKey] = '[' . $type . '] ' . $value;
                    continue;
                }

                $result[$normalizedKey] = $this->stringifyForDiff($extraValue);
            }
        }

        ksort($result);

        return $result;
    }

    private function stringifyForDiff(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value) || $value === null) {
            return trim((string) $value);
        }

        $json = json_encode($value, JSON_UNESCAPED_SLASHES);

        return $json === false ? '' : $json;
    }

    private function humanFieldName(string $key): string
    {
        $labels = [
            'company_name' => 'Unternehmensname',
            'legal_form' => 'Rechtsform',
            'owner_name' => 'Gesellschafter / Inhaber',
            'managing_director' => 'Geschaeftsfuehrer',
            'street' => 'Strasse',
            'zip_code' => 'PLZ',
            'city' => 'Stadt',
            'country' => 'Land',
            'email' => 'E-Mail',
            'phone' => 'Telefon',
            'website' => 'Webseite',
            'bank_name' => 'Bankname',
            'account_holder' => 'Kontoinhaber',
            'iban' => 'IBAN',
            'bic' => 'BIC',
            'vat_id' => 'USt-IdNr.',
            'tax_number' => 'Steuernummer',
            'registration_number' => 'Handelsregisternummer',
            'registration_court' => 'Registergericht',
            'share_capital_eur' => 'Stammkapital (EUR)',
            'founded_on' => 'Gruendungsdatum',
        ];

        if (isset($labels[$key])) {
            return $labels[$key];
        }

        if (str_starts_with($key, 'extra_fields.')) {
            return 'Zusatzfeld: ' . substr($key, strlen('extra_fields.'));
        }

        return $key;
    }
}
