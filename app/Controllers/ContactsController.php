<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Services\ContactCompanyStore;
use App\Services\ContactStatusStore;
use App\Services\ContactStore;

final class ContactsController
{
    private ContactStore $contactStore;
    private ContactStatusStore $statusStore;
    private ContactCompanyStore $companyStore;

    public function __construct()
    {
        $this->contactStore = new ContactStore();
        $this->statusStore = new ContactStatusStore();
        $this->companyStore = new ContactCompanyStore();
    }

    public function index(): string
    {
        $success = Session::get('contacts_success');
        Session::remove('contacts_success');

        $error = Session::get('contacts_error');
        Session::remove('contacts_error');

        $activeTabRaw = Session::get('contacts_active_tab');
        Session::remove('contacts_active_tab');
        $activeTab = in_array($activeTabRaw, ['contacts', 'statuses', 'companies'], true)
            ? (string) $activeTabRaw
            : 'contacts';

        $allContacts = $this->contactStore->getAll();
        $statuses = $this->statusStore->getAll();
        $companies = $this->companyStore->getAll();
        $companyNames = $this->extractCompanyNames($companies);

        $companyFilter = trim((string) ($_GET['company'] ?? ''));
        if (!in_array($companyFilter, $companyNames, true)) {
            $companyFilter = '';
        }

        $contacts = $allContacts;
        if ($companyFilter !== '') {
            $contacts = array_values(array_filter(
                $allContacts,
                static fn (array $contact): bool => (string) ($contact['company'] ?? '') === $companyFilter
            ));
        }

        $editId = trim((string) ($_GET['edit'] ?? ''));
        $editingContact = null;

        if ($editId !== '') {
            foreach ($allContacts as $contact) {
                if ((string) ($contact['id'] ?? '') === $editId) {
                    $editingContact = $contact;
                    break;
                }
            }
        }

        $editCompanyName = trim((string) ($_GET['edit_company'] ?? ''));
        $editingCompany = null;

        if ($editCompanyName !== '') {
            foreach ($companies as $company) {
                if (strtolower((string) ($company['company_name'] ?? '')) === strtolower($editCompanyName)) {
                    $editingCompany = $company;
                    $activeTab = 'companies';
                    break;
                }
            }
        }

        return (new View())->render('contacts/index', [
            'title' => 'Kontakte',
            'contacts' => $contacts,
            'contactsTotal' => count($allContacts),
            'statuses' => $statuses,
            'companies' => $companies,
            'companyNames' => $companyNames,
            'selectedCompany' => $companyFilter,
            'editingContact' => $editingContact,
            'editingCompany' => $editingCompany,
            'activeTab' => $activeTab,
            'success' => is_string($success) ? $success : null,
            'error' => is_string($error) ? $error : null,
        ]);
    }

    public function save(): void
    {
        $statuses = $this->statusStore->getAll();
        $companies = $this->companyStore->getAll();
        $companyNames = $this->extractCompanyNames($companies);

        $extraFields = $this->buildExtraFields(
            $_POST['extra_field_key'] ?? [],
            $_POST['extra_field_value'] ?? [],
            $_POST['extra_field_type'] ?? []
        );

        if ($extraFields === null) {
            Session::set('contacts_error', 'Zusatzfelder enthalten doppelte oder ungueltige Schluessel.');
            Session::set('contacts_active_tab', 'contacts');
            http_response_code(302);
            header('Location: /contacts');
            exit;
        }

        $firstName = trim((string) ($_POST['first_name'] ?? ''));
        $lastName = trim((string) ($_POST['last_name'] ?? ''));

        if ($firstName === '' && $lastName === '') {
            Session::set('contacts_error', 'Bitte mindestens Vorname oder Nachname eintragen.');
            Session::set('contacts_active_tab', 'contacts');
            http_response_code(302);
            header('Location: /contacts');
            exit;
        }

        $status = trim((string) ($_POST['status'] ?? 'Kontakt'));
        if (!in_array($status, $statuses, true)) {
            $status = $statuses[0] ?? 'Kontakt';
        }

        $company = trim((string) ($_POST['company'] ?? ''));
        if (!in_array($company, $companyNames, true)) {
            $company = '';
        }

        $street = trim((string) ($_POST['street'] ?? ''));
        $zipCode = trim((string) ($_POST['zip_code'] ?? ''));
        $city = trim((string) ($_POST['city'] ?? ''));
        $country = trim((string) ($_POST['country'] ?? ''));

        if ($street === '' || $zipCode === '' || $city === '' || $country === '') {
            Session::set('contacts_error', 'Bitte fuer Kontakte eine vollstaendige Adresse mit Strasse, PLZ, Stadt und Land erfassen.');
            Session::set('contacts_active_tab', 'contacts');
            http_response_code(302);
            header('Location: /contacts');
            exit;
        }

        $id = trim((string) ($_POST['id'] ?? ''));
        if ($id === '') {
            $id = $this->generateId();
        }

        $this->contactStore->upsert([
            'id' => $id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'street' => $street,
            'zip_code' => $zipCode,
            'city' => $city,
            'country' => $country,
            'company' => $company,
            'position' => trim((string) ($_POST['position'] ?? '')),
            'status' => $status,
            'extra_fields' => $extraFields,
        ]);

        Session::set('contacts_success', 'Kontakt wurde gespeichert.');
        Session::set('contacts_active_tab', 'contacts');
        http_response_code(302);
        header('Location: /contacts');
        exit;
    }

    public function delete(): void
    {
        $id = trim((string) ($_POST['id'] ?? ''));
        $this->contactStore->deleteById($id);

        Session::set('contacts_success', 'Kontakt wurde entfernt.');
        Session::set('contacts_active_tab', 'contacts');
        http_response_code(302);
        header('Location: /contacts');
        exit;
    }

    public function saveStatuses(): void
    {
        $rawStatuses = $_POST['status_name'] ?? [];
        if (!is_array($rawStatuses)) {
            Session::set('contacts_error', 'Statusliste ist ungueltig.');
            Session::set('contacts_active_tab', 'statuses');
            http_response_code(302);
            header('Location: /contacts');
            exit;
        }

        $statuses = [];
        $seen = [];

        foreach ($rawStatuses as $entry) {
            $label = trim((string) $entry);
            if ($label === '') {
                continue;
            }

            if (!preg_match('/^[A-Za-z0-9\s\-_]{2,40}$/', $label)) {
                Session::set('contacts_error', 'Status darf nur Buchstaben, Zahlen, Leerzeichen, Bindestriche und Unterstriche enthalten (2-40 Zeichen).');
                Session::set('contacts_active_tab', 'statuses');
                http_response_code(302);
                header('Location: /contacts');
                exit;
            }

            $key = strtolower($label);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $statuses[] = $label;
        }

        $this->statusStore->saveAll($statuses);
        $normalizedStatuses = $this->statusStore->getAll();
        $defaultStatus = $normalizedStatuses[0] ?? 'Kontakt';

        $contacts = $this->contactStore->getAll();
        foreach ($contacts as $index => $contact) {
            $current = (string) ($contact['status'] ?? '');
            if (in_array($current, $normalizedStatuses, true)) {
                continue;
            }

            $contacts[$index]['status'] = $defaultStatus;
        }
        $this->contactStore->saveAll($contacts);

        Session::set('contacts_success', 'Kontakt-Status wurden gespeichert.');
        Session::set('contacts_active_tab', 'statuses');
        http_response_code(302);
        header('Location: /contacts');
        exit;
    }

    public function saveCompanies(): void
    {
        $companyEntries = $this->buildCompanyEntries(
            $_POST['company_name'] ?? [],
            $_POST['legal_form'] ?? [],
            $_POST['street'] ?? [],
            $_POST['zip_code'] ?? [],
            $_POST['city'] ?? [],
            $_POST['country'] ?? [],
            $_POST['email'] ?? [],
            $_POST['phone'] ?? [],
            $_POST['website'] ?? [],
            $_POST['vat_id'] ?? [],
            $_POST['tax_number'] ?? [],
            $_POST['registration_number'] ?? [],
            $_POST['registration_court'] ?? []
        );

        if ($companyEntries === null) {
            Session::set('contacts_error', 'Firmenliste ist ungueltig.');
            Session::set('contacts_active_tab', 'companies');
            http_response_code(302);
            header('Location: /contacts');
            exit;
        }

        $this->companyStore->saveAll($companyEntries);
        $normalizedCompanies = $this->companyStore->getAll();
        $companyNames = $this->extractCompanyNames($normalizedCompanies);

        $contacts = $this->contactStore->getAll();
        foreach ($contacts as $index => $contact) {
            $current = (string) ($contact['company'] ?? '');
            if ($current === '' || in_array($current, $companyNames, true)) {
                continue;
            }

            $contacts[$index]['company'] = '';
        }
        $this->contactStore->saveAll($contacts);

        Session::set('contacts_success', 'Kontakt-Firmen wurden gespeichert.');
        Session::set('contacts_active_tab', 'companies');
        http_response_code(302);
        header('Location: /contacts');
        exit;
    }

    public function showContact(): string
    {
        $id = trim((string) ($_GET['id'] ?? ''));
        if ($id === '') {
            http_response_code(302);
            header('Location: /contacts');
            exit;
        }

        $contact = null;
        foreach ($this->contactStore->getAll() as $c) {
            if ((string) ($c['id'] ?? '') === $id) {
                $contact = $c;
                break;
            }
        }

        if ($contact === null) {
            http_response_code(302);
            header('Location: /contacts');
            exit;
        }

        $company = null;
        $companyName = (string) ($contact['company'] ?? '');
        if ($companyName !== '') {
            foreach ($this->companyStore->getAll() as $co) {
                if (strtolower((string) ($co['company_name'] ?? '')) === strtolower($companyName)) {
                    $company = $co;
                    break;
                }
            }
        }

        $fullName = trim((string) ($contact['first_name'] ?? '') . ' ' . (string) ($contact['last_name'] ?? ''));
        if ($fullName === '') {
            $fullName = '(ohne Namen)';
        }

        return (new View())->render('contacts/show', [
            'title' => $fullName,
            'contact' => $contact,
            'company' => $company,
        ]);
    }

    public function showCompany(): string
    {
        $name = trim((string) ($_GET['name'] ?? ''));
        if ($name === '') {
            http_response_code(302);
            header('Location: /contacts');
            exit;
        }

        $company = null;
        foreach ($this->companyStore->getAll() as $co) {
            if (strtolower((string) ($co['company_name'] ?? '')) === strtolower($name)) {
                $company = $co;
                break;
            }
        }

        if ($company === null) {
            http_response_code(302);
            header('Location: /contacts');
            exit;
        }

        $relatedContacts = array_values(array_filter(
            $this->contactStore->getAll(),
            static fn (array $c): bool => strtolower((string) ($c['company'] ?? '')) === strtolower((string) ($company['company_name'] ?? ''))
        ));

        return (new View())->render('contacts/company-show', [
            'title' => (string) ($company['company_name'] ?? 'Firma'),
            'company' => $company,
            'relatedContacts' => $relatedContacts,
        ]);
    }

    /**
     * @param list<array<string, string>> $companies
     * @return list<string>
     */
    private function extractCompanyNames(array $companies): array
    {
        $names = [];

        foreach ($companies as $company) {
            $name = trim((string) ($company['company_name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $names[] = $name;
        }

        return $names;
    }

    /**
     * @return list<array<string, string>>|null
     */
    private function buildCompanyEntries(
        mixed $rawNames,
        mixed $rawLegalForms,
        mixed $rawStreets,
        mixed $rawZipCodes,
        mixed $rawCities,
        mixed $rawCountries,
        mixed $rawEmails,
        mixed $rawPhones,
        mixed $rawWebsites,
        mixed $rawVatIds,
        mixed $rawTaxNumbers,
        mixed $rawRegistrationNumbers,
        mixed $rawRegistrationCourts
    ): ?array {
        if (
            !is_array($rawNames)
            || !is_array($rawLegalForms)
            || !is_array($rawStreets)
            || !is_array($rawZipCodes)
            || !is_array($rawCities)
            || !is_array($rawCountries)
            || !is_array($rawEmails)
            || !is_array($rawPhones)
            || !is_array($rawWebsites)
            || !is_array($rawVatIds)
            || !is_array($rawTaxNumbers)
            || !is_array($rawRegistrationNumbers)
            || !is_array($rawRegistrationCourts)
        ) {
            return null;
        }

        $entries = [];
        $seen = [];

        $count = max(
            count($rawNames),
            count($rawLegalForms),
            count($rawStreets),
            count($rawZipCodes),
            count($rawCities),
            count($rawCountries),
            count($rawEmails),
            count($rawPhones),
            count($rawWebsites),
            count($rawVatIds),
            count($rawTaxNumbers),
            count($rawRegistrationNumbers),
            count($rawRegistrationCourts)
        );

        for ($index = 0; $index < $count; $index++) {
            $name = trim((string) ($rawNames[$index] ?? ''));
            $legalForm = trim((string) ($rawLegalForms[$index] ?? ''));
            $street = trim((string) ($rawStreets[$index] ?? ''));
            $zipCode = trim((string) ($rawZipCodes[$index] ?? ''));
            $city = trim((string) ($rawCities[$index] ?? ''));
            $country = trim((string) ($rawCountries[$index] ?? ''));
            $email = trim((string) ($rawEmails[$index] ?? ''));
            $phone = trim((string) ($rawPhones[$index] ?? ''));
            $website = trim((string) ($rawWebsites[$index] ?? ''));
            $vatId = trim((string) ($rawVatIds[$index] ?? ''));
            $taxNumber = trim((string) ($rawTaxNumbers[$index] ?? ''));
            $registrationNumber = trim((string) ($rawRegistrationNumbers[$index] ?? ''));
            $registrationCourt = trim((string) ($rawRegistrationCourts[$index] ?? ''));

            $allEmpty = $name === ''
                && $legalForm === ''
                && $street === ''
                && $zipCode === ''
                && $city === ''
                && $country === ''
                && $email === ''
                && $phone === ''
                && $website === ''
                && $vatId === ''
                && $taxNumber === ''
                && $registrationNumber === ''
                && $registrationCourt === '';

            if ($allEmpty) {
                continue;
            }

            if ($name === '') {
                return null;
            }

            if (!preg_match('/^[A-Za-z0-9\s\-_.&,()]{2,80}$/', $name)) {
                return null;
            }

            $nameKey = strtolower($name);
            if (isset($seen[$nameKey])) {
                continue;
            }
            $seen[$nameKey] = true;

            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                return null;
            }

            if ($website !== '' && filter_var($website, FILTER_VALIDATE_URL) === false) {
                return null;
            }

            $entries[] = [
                'company_name' => $name,
                'legal_form' => $legalForm,
                'street' => $street,
                'zip_code' => $zipCode,
                'city' => $city,
                'country' => $country,
                'email' => $email,
                'phone' => $phone,
                'website' => $website,
                'vat_id' => $vatId,
                'tax_number' => $taxNumber,
                'registration_number' => $registrationNumber,
                'registration_court' => $registrationCourt,
            ];
        }

        return $entries;
    }

    private function generateId(): string
    {
        try {
            return bin2hex(random_bytes(8));
        } catch (\Exception) {
            return str_replace('.', '', uniqid('contact_', true));
        }
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

    public function addStatus(): void
    {
        $label = trim((string) ($_POST['status_name'] ?? ''));

        if ($label === '' || !preg_match('/^[A-Za-z0-9\s\-_]{2,40}$/', $label)) {
            Session::set('contacts_error', 'Bitte einen gueltigen Statusnamen eingeben (2–40 Zeichen, nur Buchstaben/Zahlen/Leerzeichen/-/_).');
            Session::set('contacts_active_tab', 'statuses');
            http_response_code(302);
            header('Location: /contacts');
            exit;
        }

        $statuses = $this->statusStore->getAll();
        $statuses[] = $label;
        $this->statusStore->saveAll($statuses);

        Session::set('contacts_success', 'Status "' . $label . '" wurde angelegt.');
        Session::set('contacts_active_tab', 'statuses');
        http_response_code(302);
        header('Location: /contacts');
        exit;
    }

    public function deleteStatus(): void
    {
        $label = trim((string) ($_POST['status_name'] ?? ''));

        $statuses = $this->statusStore->getAll();
        $filtered = array_values(array_filter(
            $statuses,
            static fn (string $s): bool => strtolower($s) !== strtolower($label)
        ));
        $this->statusStore->saveAll($filtered);

        $normalizedStatuses = $this->statusStore->getAll();
        $defaultStatus = $normalizedStatuses[0] ?? 'Kontakt';

        $contacts = $this->contactStore->getAll();
        foreach ($contacts as $index => $contact) {
            $current = (string) ($contact['status'] ?? '');
            if (in_array($current, $normalizedStatuses, true)) {
                continue;
            }
            $contacts[$index]['status'] = $defaultStatus;
        }
        $this->contactStore->saveAll($contacts);

        Session::set('contacts_success', 'Status wurde entfernt.');
        Session::set('contacts_active_tab', 'statuses');
        http_response_code(302);
        header('Location: /contacts');
        exit;
    }

    public function saveCompany(): void
    {
        $extraFields = $this->buildExtraFields(
            $_POST['extra_field_key'] ?? [],
            $_POST['extra_field_value'] ?? [],
            $_POST['extra_field_type'] ?? []
        );

        if ($extraFields === null) {
            Session::set('contacts_error', 'Firmen-Zusatzfelder enthalten ungueltige Schluessel.');
            Session::set('contacts_active_tab', 'companies');
            http_response_code(302);
            header('Location: /contacts');
            exit;
        }

        $originalName = trim((string) ($_POST['original_name'] ?? ''));
        $name = trim((string) ($_POST['company_name'] ?? ''));
        $allowedLegalForms = ['Freelancer', 'Einzelunternehmen', 'GbR', 'UG (haftungsbeschraenkt)', 'GmbH'];
        $legalForm = trim((string) ($_POST['legal_form'] ?? ''));
        if (!in_array($legalForm, $allowedLegalForms, true)) {
            $legalForm = '';
        }
        $ownerName = trim((string) ($_POST['owner_name'] ?? ''));
        $managingDirector = trim((string) ($_POST['managing_director'] ?? ''));
        $foundedOn = trim((string) ($_POST['founded_on'] ?? ''));
        $street = trim((string) ($_POST['street'] ?? ''));
        $zipCode = trim((string) ($_POST['zip_code'] ?? ''));
        $city = trim((string) ($_POST['city'] ?? ''));
        $country = trim((string) ($_POST['country'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $website = trim((string) ($_POST['website'] ?? ''));
        $vatId = trim((string) ($_POST['vat_id'] ?? ''));
        $taxNumber = trim((string) ($_POST['tax_number'] ?? ''));
        $registrationNumber = trim((string) ($_POST['registration_number'] ?? ''));
        $registrationCourt = trim((string) ($_POST['registration_court'] ?? ''));
        $shareCapital = trim((string) ($_POST['share_capital_eur'] ?? ''));

        // Apply legal-form normalization (clear irrelevant fields).
        if ($legalForm === 'Freelancer') {
            $registrationNumber = '';
            $registrationCourt = '';
            $shareCapital = '';
            $managingDirector = '';
        } elseif (in_array($legalForm, ['Einzelunternehmen', 'GbR'], true)) {
            $shareCapital = '';
            $managingDirector = '';
        } elseif (in_array($legalForm, ['UG (haftungsbeschraenkt)', 'GmbH'], true)) {
            $ownerName = '';
        }

        if ($name === '' || !preg_match('/^[A-Za-z0-9\s\-_.&,()]{2,80}$/', $name)) {
            Session::set('contacts_error', 'Bitte einen gueltigen Firmennamen eingeben (2–80 Zeichen).');
            Session::set('contacts_active_tab', 'companies');
            http_response_code(302);
            header('Location: /contacts');
            exit;
        }

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            Session::set('contacts_error', 'Die E-Mail-Adresse der Firma ist ungueltig.');
            Session::set('contacts_active_tab', 'companies');
            http_response_code(302);
            header('Location: /contacts');
            exit;
        }

        if ($website !== '' && filter_var($website, FILTER_VALIDATE_URL) === false) {
            Session::set('contacts_error', 'Die Webseite-URL der Firma ist ungueltig.');
            Session::set('contacts_active_tab', 'companies');
            http_response_code(302);
            header('Location: /contacts');
            exit;
        }

        $newRecord = [
            'company_name' => $name,
            'legal_form' => $legalForm,
            'owner_name' => $ownerName,
            'managing_director' => $managingDirector,
            'founded_on' => $foundedOn,
            'street' => $street,
            'zip_code' => $zipCode,
            'city' => $city,
            'country' => $country,
            'email' => $email,
            'phone' => $phone,
            'website' => $website,
            'vat_id' => $vatId,
            'tax_number' => $taxNumber,
            'registration_number' => $registrationNumber,
            'registration_court' => $registrationCourt,
            'share_capital_eur' => $shareCapital,
            'extra_fields' => $extraFields,
        ];

        $companies = $this->companyStore->getAll();
        $found = false;

        if ($originalName !== '') {
            foreach ($companies as $index => $company) {
                if (strtolower((string) ($company['company_name'] ?? '')) === strtolower($originalName)) {
                    $companies[$index] = $newRecord;
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            foreach ($companies as $company) {
                if (strtolower((string) ($company['company_name'] ?? '')) === strtolower($name)) {
                    Session::set('contacts_error', 'Eine Firma mit diesem Namen existiert bereits.');
                    Session::set('contacts_active_tab', 'companies');
                    http_response_code(302);
                    header('Location: /contacts');
                    exit;
                }
            }
            $companies[] = $newRecord;
        }

        $this->companyStore->saveAll($companies);

        if ($originalName !== '' && strtolower($originalName) !== strtolower($name)) {
            $contacts = $this->contactStore->getAll();
            foreach ($contacts as $index => $contact) {
                if (strtolower((string) ($contact['company'] ?? '')) === strtolower($originalName)) {
                    $contacts[$index]['company'] = $name;
                }
            }
            $this->contactStore->saveAll($contacts);
        }

        Session::set('contacts_success', 'Firma "' . $name . '" wurde gespeichert.');
        Session::set('contacts_active_tab', 'companies');
        http_response_code(302);
        header('Location: /contacts');
        exit;
    }

    public function deleteCompany(): void
    {
        $name = trim((string) ($_POST['company_name'] ?? ''));

        $companies = $this->companyStore->getAll();
        $filtered = array_values(array_filter(
            $companies,
            static fn (array $c): bool => strtolower((string) ($c['company_name'] ?? '')) !== strtolower($name)
        ));
        $this->companyStore->saveAll($filtered);

        $normalizedCompanies = $this->companyStore->getAll();
        $companyNames = $this->extractCompanyNames($normalizedCompanies);

        $contacts = $this->contactStore->getAll();
        foreach ($contacts as $index => $contact) {
            $current = (string) ($contact['company'] ?? '');
            if ($current === '' || in_array($current, $companyNames, true)) {
                continue;
            }
            $contacts[$index]['company'] = '';
        }
        $this->contactStore->saveAll($contacts);

        Session::set('contacts_success', 'Firma wurde entfernt.');
        Session::set('contacts_active_tab', 'companies');
        http_response_code(302);
        header('Location: /contacts');
        exit;
    }
}
