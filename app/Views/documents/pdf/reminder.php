<?php

declare(strict_types=1);

/** @var array<string, mixed> $document */
/** @var array<string, mixed> $companyProfile */
/** @var array{name: string, street: string, zip_code: string, city: string, country: string} $recipientProfile */

$companyName = (string) ($companyProfile['company_name'] ?? 'Unternehmen');
$address = trim((string) ($companyProfile['street'] ?? '') . ', ' . (string) ($companyProfile['zip_code'] ?? '') . ' ' . (string) ($companyProfile['city'] ?? ''));
$recipientAddressLine2 = trim((string) ($recipientProfile['zip_code'] ?? '') . ' ' . (string) ($recipientProfile['city'] ?? ''));
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        h1 { margin: 0 0 8px; font-size: 24px; }
        h2 { margin: 20px 0 8px; font-size: 14px; }
        .muted { color: #6b7280; }
        .box { border: 1px solid #e5e7eb; padding: 10px; border-radius: 6px; }
        .row { margin-bottom: 6px; }
        .total { font-size: 16px; font-weight: bold; margin-top: 12px; }
    </style>
</head>
<body>
    <h1>Mahnung</h1>
    <div class="row"><strong>Nummer:</strong> <?= $this->escape((string) ($document['number'] ?? '-')) ?></div>
    <div class="row"><strong>Erstellt am:</strong> <?= $this->escape((string) ($document['created_at'] ?? '-')) ?></div>

    <h2>Aussteller</h2>
    <div class="box">
        <div class="row"><strong><?= $this->escape($companyName) ?></strong></div>
        <div class="row muted"><?= $this->escape($address) ?></div>
        <div class="row muted"><?= $this->escape((string) ($companyProfile['email'] ?? '')) ?><?= (string) ($companyProfile['phone'] ?? '') !== '' ? ' · ' . $this->escape((string) $companyProfile['phone']) : '' ?></div>
    </div>

    <h2>Empfaenger</h2>
    <div class="box">
        <div class="row"><strong><?= $this->escape((string) ($recipientProfile['name'] ?? (string) ($document['customer_name'] ?? '-'))) ?></strong></div>
        <?php if ((string) ($recipientProfile['street'] ?? '') !== ''): ?>
            <div class="row"><?= $this->escape((string) ($recipientProfile['street'] ?? '')) ?></div>
        <?php endif; ?>
        <?php if ($recipientAddressLine2 !== ''): ?>
            <div class="row"><?= $this->escape($recipientAddressLine2) ?></div>
        <?php endif; ?>
        <?php if ((string) ($recipientProfile['country'] ?? '') !== ''): ?>
            <div class="row"><?= $this->escape((string) ($recipientProfile['country'] ?? '')) ?></div>
        <?php endif; ?>
    </div>

    <h2>Mahndetails</h2>
    <div class="box">
        <div class="row"><strong>Rechnungsnummer:</strong> <?= $this->escape((string) ($document['invoice_number'] ?? '-')) ?></div>
        <div class="row"><strong>Mahnstufe:</strong> <?= $this->escape((string) ($document['level'] ?? '1')) ?></div>
        <div class="row"><strong>Frist:</strong> <?= $this->escape((string) ($document['due_date'] ?? '-')) ?></div>
        <div class="row"><strong>Status:</strong> <?= $this->escape((string) ($document['status'] ?? 'Verschickt')) ?></div>
        <div class="total">Offener Betrag: <?= $this->escape((string) ($document['amount_eur'] ?? '0.00')) ?> EUR</div>
    </div>

    <?php if ((string) ($document['notes'] ?? '') !== ''): ?>
        <h2>Notiz</h2>
        <div class="box"><?= nl2br($this->escape((string) ($document['notes'] ?? ''))) ?></div>
    <?php endif; ?>
</body>
</html>
