<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Services\ContactCompanyStore;
use App\Services\ContactStore;
use App\Services\CompanyProfileStore;
use App\Services\DocumentStore;
use App\Services\NumberSequenceStore;
use Dompdf\Dompdf;
use Dompdf\Options;

final class DocumentsController
{
    private DocumentStore $store;

    public function __construct()
    {
        $this->store = new DocumentStore();
    }

    public function index(): string
    {
        $success = Session::get('documents_success');
        Session::remove('documents_success');

        $error = Session::get('documents_error');
        Session::remove('documents_error');

        $data = $this->store->getAll();
        $customerOptions = $this->buildCustomerOptions();

        return (new View())->render('documents/index', [
            'title' => 'Dokumente',
            'offers' => $data['offers'],
            'invoices' => $data['invoices'],
            'reminders' => $data['reminders'],
            'customerOptions' => $customerOptions,
            'success' => is_string($success) ? $success : null,
            'error' => is_string($error) ? $error : null,
        ]);
    }

    public function saveOffer(): void
    {
        $customerName = trim((string) ($_POST['customer_name'] ?? ''));
        $amount = trim((string) ($_POST['amount_eur'] ?? ''));
        $validUntil = trim((string) ($_POST['valid_until'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));

        if ($customerName === '' || !$this->isValidAmount($amount)) {
            Session::set('documents_error', 'Bitte fuer das Angebot mindestens Kunde und gueltigen Betrag eintragen.');
            $this->redirectDocuments();
        }

        // Assign customer number first so resolveRecipient can pick it up
        $this->ensureCustomerNumber($customerName);
        $recipientProfile = $this->resolveRecipientByCustomerName($customerName);

        $this->store->addOffer([
            'id' => $this->generateId(),
            'number' => $this->generateNumber('ANG'),
            'customer_name' => $customerName,
            'recipient_name' => $recipientProfile['name'],
            'recipient_street' => $recipientProfile['street'],
            'recipient_zip_code' => $recipientProfile['zip_code'],
            'recipient_city' => $recipientProfile['city'],
            'recipient_country' => $recipientProfile['country'],
            'recipient_customer_number' => $recipientProfile['customer_number'],
            'amount_eur' => $this->normalizeAmount($amount),
            'valid_until' => $validUntil,
            'created_at' => date('Y-m-d H:i'),
            'status' => 'Entwurf',
            'notes' => $notes,
        ]);

        Session::set('documents_success', 'Angebot wurde erstellt.');
        $this->redirectDocuments();
    }

    public function saveInvoice(): void
    {
        $customerName = trim((string) ($_POST['customer_name'] ?? ''));
        $dueDate = trim((string) ($_POST['due_date'] ?? ''));
        $discountPercentRaw = trim((string) ($_POST['discount_percent'] ?? '0'));
        $vatPercentRaw = trim((string) ($_POST['vat_percent'] ?? '19'));
        $notes = trim((string) ($_POST['notes'] ?? ''));
        $lineItems = $this->buildInvoiceItems(
            $_POST['item_description'] ?? [],
            $_POST['item_quantity'] ?? [],
            $_POST['item_unit_price'] ?? []
        );

        if ($customerName === '') {
            Session::set('documents_error', 'Bitte einen Kunden fuer die Rechnung auswaehlen.');
            $this->redirectDocuments();
        }

        if ($lineItems === []) {
            Session::set('documents_error', 'Bitte mindestens eine gueltige Rechnungsposition anlegen.');
            $this->redirectDocuments();
        }

        // Assign customer number first so resolveRecipient can pick it up
        $this->ensureCustomerNumber($customerName);
        $recipientProfile = $this->resolveRecipientByCustomerName($customerName);

        $discountPercent = $this->normalizePercent($discountPercentRaw);
        $vatPercent = $this->normalizePercent($vatPercentRaw);

        $subTotal = 0.0;
        foreach ($lineItems as $item) {
            $subTotal += (float) ($item['line_total'] ?? '0');
        }

        $discountAmount = $subTotal * ($discountPercent / 100);
        $netAfterDiscount = max(0.0, $subTotal - $discountAmount);
        $vatAmount = $netAfterDiscount * ($vatPercent / 100);
        $grossTotal = $netAfterDiscount + $vatAmount;

        $this->store->addInvoice([
            'id' => $this->generateId(),
            'number' => $this->generateNumber('RE'),
            'customer_name' => $customerName,
            'recipient_name' => $recipientProfile['name'],
            'recipient_street' => $recipientProfile['street'],
            'recipient_zip_code' => $recipientProfile['zip_code'],
            'recipient_city' => $recipientProfile['city'],
            'recipient_country' => $recipientProfile['country'],
            'recipient_customer_number' => $recipientProfile['customer_number'],
            'line_items' => $lineItems,
            'sub_total_eur' => $this->normalizeAmount((string) $subTotal),
            'discount_percent' => $this->normalizeAmount((string) $discountPercent),
            'discount_amount_eur' => $this->normalizeAmount((string) $discountAmount),
            'net_total_eur' => $this->normalizeAmount((string) $netAfterDiscount),
            'vat_percent' => $this->normalizeAmount((string) $vatPercent),
            'vat_amount_eur' => $this->normalizeAmount((string) $vatAmount),
            'gross_total_eur' => $this->normalizeAmount((string) $grossTotal),
            'due_date' => $dueDate,
            'created_at' => date('Y-m-d H:i'),
            'status' => 'Offen',
            'notes' => $notes,
        ]);

        Session::set('documents_success', 'Rechnung wurde erstellt.');
        $this->redirectDocuments();
    }

    public function saveReminder(): void
    {
        $customerName = trim((string) ($_POST['customer_name'] ?? ''));
        $invoiceNumber = trim((string) ($_POST['invoice_number'] ?? ''));
        $amount = trim((string) ($_POST['amount_eur'] ?? ''));
        $level = trim((string) ($_POST['level'] ?? '1'));
        $dueDate = trim((string) ($_POST['due_date'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));

        if ($customerName === '' || $invoiceNumber === '' || !$this->isValidAmount($amount)) {
            Session::set('documents_error', 'Bitte fuer die Mahnung Kunde, Rechnungsnummer und gueltigen Betrag eintragen.');
            $this->redirectDocuments();
        }

        if (!in_array($level, ['1', '2', '3'], true)) {
            $level = '1';
        }

        // Assign customer number first so resolveRecipient can pick it up
        $this->ensureCustomerNumber($customerName);
        $recipientProfile = $this->resolveRecipientByCustomerName($customerName);

        $this->store->addReminder([
            'id' => $this->generateId(),
            'number' => $this->generateNumber('MAH'),
            'customer_name' => $customerName,
            'recipient_name' => $recipientProfile['name'],
            'recipient_street' => $recipientProfile['street'],
            'recipient_zip_code' => $recipientProfile['zip_code'],
            'recipient_city' => $recipientProfile['city'],
            'recipient_country' => $recipientProfile['country'],
            'recipient_customer_number' => $recipientProfile['customer_number'],
            'invoice_number' => $invoiceNumber,
            'amount_eur' => $this->normalizeAmount($amount),
            'level' => $level,
            'due_date' => $dueDate,
            'created_at' => date('Y-m-d H:i'),
            'status' => 'Verschickt',
            'notes' => $notes,
        ]);

        Session::set('documents_success', 'Mahnung wurde erstellt.');
        $this->redirectDocuments();
    }

    public function deleteEntry(): void
    {
        $type = trim((string) ($_POST['type'] ?? ''));
        $id = trim((string) ($_POST['id'] ?? ''));

        $this->store->deleteEntry($type, $id);
        Session::set('documents_success', 'Dokument wurde entfernt.');
        $this->redirectDocuments();
    }

    public function downloadPdf(): void
    {
        $type = trim((string) ($_GET['type'] ?? ''));
        $id = trim((string) ($_GET['id'] ?? ''));

        $entry = $this->store->findEntry($type, $id);
        if ($entry === null) {
            Session::set('documents_error', 'Dokument wurde fuer den PDF-Export nicht gefunden.');
            $this->redirectDocuments();
        }

        $templateByType = [
            'offers' => 'documents/pdf/offer',
            'invoices' => 'documents/pdf/invoice',
            'reminders' => 'documents/pdf/reminder',
        ];

        if (!isset($templateByType[$type])) {
            Session::set('documents_error', 'Unbekannter Dokumenttyp fuer PDF-Export.');
            $this->redirectDocuments();
        }

        $companyProfile = (new CompanyProfileStore())->get();
        $recipientProfile = $this->buildRecipientProfile($entry);

        $html = (new View())->render($templateByType[$type], [
            'document' => $entry,
            'companyProfile' => $companyProfile,
            'recipientProfile' => $recipientProfile,
        ], null);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $safeNumber = preg_replace('/[^A-Za-z0-9\-_]/', '_', (string) ($entry['number'] ?? 'dokument'));
        $fileName = ($safeNumber !== null && $safeNumber !== '') ? $safeNumber : 'dokument';

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $fileName . '.pdf"');
        echo $dompdf->output();
        exit;
    }

    private function redirectDocuments(): void
    {
        http_response_code(302);
        header('Location: /documents');
        exit;
    }

    private function generateId(): string
    {
        try {
            return bin2hex(random_bytes(8));
        } catch (\Exception) {
            return str_replace('.', '', uniqid('doc_', true));
        }
    }

    private function generateNumber(string $prefix): string
    {
        return $prefix . '-' . date('Ymd-His') . '-' . substr($this->generateId(), 0, 4);
    }

    private function isValidAmount(string $amount): bool
    {
        if ($amount === '') {
            return false;
        }

        return is_numeric(str_replace(',', '.', $amount));
    }

    private function normalizeAmount(string $amount): string
    {
        $normalized = str_replace(',', '.', $amount);
        return number_format((float) $normalized, 2, '.', '');
    }

    private function normalizePercent(string $value): float
    {
        $normalized = (float) str_replace(',', '.', $value);
        if ($normalized < 0) {
            return 0.0;
        }

        if ($normalized > 100) {
            return 100.0;
        }

        return $normalized;
    }

    /**
     * @param mixed $descriptions
     * @param mixed $quantities
     * @param mixed $unitPrices
     * @return list<array{description: string, quantity: string, unit_price_eur: string, line_total: string}>
     */
    private function buildInvoiceItems(mixed $descriptions, mixed $quantities, mixed $unitPrices): array
    {
        if (!is_array($descriptions) || !is_array($quantities) || !is_array($unitPrices)) {
            return [];
        }

        $items = [];
        $count = max(count($descriptions), count($quantities), count($unitPrices));

        for ($index = 0; $index < $count; $index++) {
            $description = trim((string) ($descriptions[$index] ?? ''));
            $qtyRaw = trim((string) ($quantities[$index] ?? ''));
            $unitRaw = trim((string) ($unitPrices[$index] ?? ''));

            if ($description === '' && $qtyRaw === '' && $unitRaw === '') {
                continue;
            }

            $qty = (float) str_replace(',', '.', $qtyRaw);
            $unit = (float) str_replace(',', '.', $unitRaw);

            if ($description === '' || $qty <= 0 || $unit < 0) {
                continue;
            }

            $lineTotal = $qty * $unit;

            $items[] = [
                'description' => $description,
                'quantity' => $this->normalizeAmount((string) $qty),
                'unit_price_eur' => $this->normalizeAmount((string) $unit),
                'line_total' => $this->normalizeAmount((string) $lineTotal),
            ];
        }

        return $items;
    }

    /**
     * @return list<string>
     */
    private function buildCustomerOptions(): array
    {
        $options = [];
        $seen = [];

        $companies = (new ContactCompanyStore())->getAll();
        foreach ($companies as $company) {
            $name = trim((string) ($company['company_name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $key = strtolower($name);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $options[] = $name;
        }

        $contacts = (new ContactStore())->getAll();
        foreach ($contacts as $contact) {
            $fullName = trim((string) ($contact['first_name'] ?? '') . ' ' . (string) ($contact['last_name'] ?? ''));
            if ($fullName === '') {
                continue;
            }

            $company = trim((string) ($contact['company'] ?? ''));
            $label = $company !== '' ? $fullName . ' (' . $company . ')' : $fullName;

            $key = strtolower($label);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $options[] = $label;
        }

        sort($options);

        return $options;
    }

    /**
     * @return array{name: string, street: string, zip_code: string, city: string, country: string, customer_number: string}
     */
    private function resolveRecipientByCustomerName(string $customerName): array
    {
        $customerName = trim($customerName);
        if ($customerName === '') {
            return [
                'name' => '',
                'street' => '',
                'zip_code' => '',
                'city' => '',
                'country' => '',
                'customer_number' => '',
            ];
        }

        foreach ((new ContactCompanyStore())->getAll() as $company) {
            $companyName = trim((string) ($company['company_name'] ?? ''));
            if (strcasecmp($companyName, $customerName) !== 0) {
                continue;
            }

            return [
                'name' => $companyName,
                'street' => trim((string) ($company['street'] ?? '')),
                'zip_code' => trim((string) ($company['zip_code'] ?? '')),
                'city' => trim((string) ($company['city'] ?? '')),
                'country' => trim((string) ($company['country'] ?? '')),
                'customer_number' => '',
            ];
        }

        foreach ((new ContactStore())->getAll() as $contact) {
            $fullName = trim((string) ($contact['first_name'] ?? '') . ' ' . (string) ($contact['last_name'] ?? ''));
            $company = trim((string) ($contact['company'] ?? ''));
            $label = $company !== '' ? $fullName . ' (' . $company . ')' : $fullName;

            if (strcasecmp($label, $customerName) !== 0 && strcasecmp($fullName, $customerName) !== 0) {
                continue;
            }

            return [
                'name' => $fullName !== '' ? $fullName : $customerName,
                'street' => trim((string) ($contact['street'] ?? '')),
                'zip_code' => trim((string) ($contact['zip_code'] ?? '')),
                'city' => trim((string) ($contact['city'] ?? '')),
                'country' => trim((string) ($contact['country'] ?? '')),
                'customer_number' => trim((string) ($contact['customer_number'] ?? '')),
            ];
        }

        return [
            'name' => $customerName,
            'street' => '',
            'zip_code' => '',
            'city' => '',
            'country' => '',
            'customer_number' => '',
        ];
    }

    /**
     * @param array<string, mixed> $document
     * @return array{name: string, street: string, zip_code: string, city: string, country: string}
     */
    private function buildRecipientProfile(array $document): array
    {
        $snapshot = [
            'name' => trim((string) ($document['recipient_name'] ?? (string) ($document['customer_name'] ?? ''))),
            'street' => trim((string) ($document['recipient_street'] ?? '')),
            'zip_code' => trim((string) ($document['recipient_zip_code'] ?? '')),
            'city' => trim((string) ($document['recipient_city'] ?? '')),
            'country' => trim((string) ($document['recipient_country'] ?? '')),
        ];

        if ($snapshot['street'] !== '' || $snapshot['zip_code'] !== '' || $snapshot['city'] !== '') {
            return $snapshot;
        }

        $customerName = trim((string) ($document['customer_name'] ?? ''));
        if ($customerName === '') {
            return $snapshot;
        }

        foreach ((new ContactCompanyStore())->getAll() as $company) {
            $companyName = trim((string) ($company['company_name'] ?? ''));
            if (strcasecmp($companyName, $customerName) !== 0) {
                continue;
            }

            return [
                'name' => $companyName,
                'street' => trim((string) ($company['street'] ?? '')),
                'zip_code' => trim((string) ($company['zip_code'] ?? '')),
                'city' => trim((string) ($company['city'] ?? '')),
                'country' => trim((string) ($company['country'] ?? '')),
            ];
        }

        foreach ((new ContactStore())->getAll() as $contact) {
            $fullName = trim((string) ($contact['first_name'] ?? '') . ' ' . (string) ($contact['last_name'] ?? ''));
            $company = trim((string) ($contact['company'] ?? ''));
            $label = $company !== '' ? $fullName . ' (' . $company . ')' : $fullName;

            if (strcasecmp($label, $customerName) !== 0 && strcasecmp($fullName, $customerName) !== 0) {
                continue;
            }

            return [
                'name' => $fullName !== '' ? $fullName : $customerName,
                'street' => trim((string) ($contact['street'] ?? '')),
                'zip_code' => trim((string) ($contact['zip_code'] ?? '')),
                'city' => trim((string) ($contact['city'] ?? '')),
                'country' => trim((string) ($contact['country'] ?? '')),
            ];
        }

        return $snapshot;
    }

    /**
     * Ensures a customer has a customer number assigned.
     * If the customer doesn't exist or doesn't have a number, generates one.
     *
     * @param string $customerName
     */
    private function ensureCustomerNumber(string $customerName): void
    {
        $customerName = trim($customerName);
        if ($customerName === '') {
            return;
        }

        $contactStore = new ContactStore();
        $sequenceStore = new NumberSequenceStore();

        foreach ($contactStore->getAll() as $contact) {
            $fullName = trim((string) ($contact['first_name'] ?? '') . ' ' . (string) ($contact['last_name'] ?? ''));
            $company = trim((string) ($contact['company'] ?? ''));
            $label = $company !== '' ? $fullName . ' (' . $company . ')' : $fullName;

            if (strcasecmp($label, $customerName) !== 0 && strcasecmp($fullName, $customerName) !== 0) {
                continue;
            }

            // Found the contact
            $customerNumber = trim((string) ($contact['customer_number'] ?? ''));
            if ($customerNumber !== '') {
                // Customer already has a number
                return;
            }

            // Assign new customer number
            $nextNumber = $sequenceStore->getNextCustomerNumber();
            $contact['customer_number'] = $nextNumber;
            $contactStore->upsert($contact);
            return;
        }
    }

    public function editInvoice(): string
    {
        $id = trim((string) ($_GET['id'] ?? ''));
        $invoice = $this->store->findEntry('invoices', $id);

        if ($invoice === null) {
            Session::set('documents_error', 'Rechnung nicht gefunden.');
            $this->redirectDocuments();
        }

        if (!$this->isWithin12Hours((string) ($invoice['created_at'] ?? ''))) {
            Session::set('documents_error', 'Diese Rechnung kann nicht mehr bearbeitet werden (12-Stunden-Fenster abgelaufen).');
            $this->redirectDocuments();
        }

        $error = Session::get('edit_invoice_error');
        Session::remove('edit_invoice_error');

        return (new View())->render('documents/edit-invoice', [
            'title' => 'Rechnung bearbeiten',
            'invoice' => $invoice,
            'customerOptions' => $this->buildCustomerOptions(),
            'error' => is_string($error) ? $error : null,
        ]);
    }

    public function updateInvoice(): void
    {
        $id = trim((string) ($_POST['id'] ?? ''));
        $existing = $this->store->findEntry('invoices', $id);

        if ($existing === null) {
            Session::set('documents_error', 'Rechnung zum Bearbeiten nicht gefunden.');
            $this->redirectDocuments();
        }

        if (!$this->isWithin12Hours((string) ($existing['created_at'] ?? ''))) {
            Session::set('documents_error', 'Die Bearbeitungszeit von 12 Stunden ist abgelaufen.');
            $this->redirectDocuments();
        }

        $customerName = trim((string) ($_POST['customer_name'] ?? ''));
        $dueDate = trim((string) ($_POST['due_date'] ?? ''));
        $discountPercentRaw = trim((string) ($_POST['discount_percent'] ?? '0'));
        $vatPercentRaw = trim((string) ($_POST['vat_percent'] ?? '19'));
        $notes = trim((string) ($_POST['notes'] ?? ''));
        $lineItems = $this->buildInvoiceItems(
            $_POST['item_description'] ?? [],
            $_POST['item_quantity'] ?? [],
            $_POST['item_unit_price'] ?? []
        );

        if ($customerName === '') {
            Session::set('edit_invoice_error', 'Bitte einen Kunden angeben.');
            $this->redirectTo('/documents/invoices/edit?id=' . urlencode($id));
        }

        if ($lineItems === []) {
            Session::set('edit_invoice_error', 'Mindestens eine gueltige Position erforderlich.');
            $this->redirectTo('/documents/invoices/edit?id=' . urlencode($id));
        }

        $recipientProfile = $this->resolveRecipientByCustomerName($customerName);
        $discountPercent = $this->normalizePercent($discountPercentRaw);
        $vatPercent = $this->normalizePercent($vatPercentRaw);

        $subTotal = 0.0;
        foreach ($lineItems as $item) {
            $subTotal += (float) ($item['line_total'] ?? '0');
        }

        $discountAmount = $subTotal * ($discountPercent / 100);
        $netAfterDiscount = max(0.0, $subTotal - $discountAmount);
        $vatAmount = $netAfterDiscount * ($vatPercent / 100);
        $grossTotal = $netAfterDiscount + $vatAmount;

        $updated = array_merge($existing, [
            'customer_name' => $customerName,
            'recipient_name' => $recipientProfile['name'],
            'recipient_street' => $recipientProfile['street'],
            'recipient_zip_code' => $recipientProfile['zip_code'],
            'recipient_city' => $recipientProfile['city'],
            'recipient_country' => $recipientProfile['country'],
            'recipient_customer_number' => $recipientProfile['customer_number'],
            'line_items' => $lineItems,
            'sub_total_eur' => $this->normalizeAmount((string) $subTotal),
            'discount_percent' => $this->normalizeAmount((string) $discountPercent),
            'discount_amount_eur' => $this->normalizeAmount((string) $discountAmount),
            'net_total_eur' => $this->normalizeAmount((string) $netAfterDiscount),
            'vat_percent' => $this->normalizeAmount((string) $vatPercent),
            'vat_amount_eur' => $this->normalizeAmount((string) $vatAmount),
            'gross_total_eur' => $this->normalizeAmount((string) $grossTotal),
            'due_date' => $dueDate,
            'notes' => $notes,
        ]);

        $this->store->updateInvoice($id, $updated);
        Session::set('documents_success', 'Rechnung wurde aktualisiert.');
        $this->redirectDocuments();
    }

    private function isWithin12Hours(string $createdAt): bool
    {
        if ($createdAt === '') {
            return false;
        }

        try {
            $created = new \DateTimeImmutable($createdAt);
            $diff = (new \DateTimeImmutable())->getTimestamp() - $created->getTimestamp();
            return $diff >= 0 && $diff <= 43200;
        } catch (\Exception) {
            return false;
        }
    }

    private function redirectTo(string $url): never
    {
        http_response_code(302);
        header('Location: ' . $url);
        exit;
    }
}
