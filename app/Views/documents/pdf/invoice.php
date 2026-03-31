<?php

declare(strict_types=1);

/** @var array<string, mixed> $document */
/** @var array<string, mixed> $companyProfile */
/** @var array{name: string, street: string, zip_code: string, city: string, country: string} $recipientProfile */

$companyName = (string) ($companyProfile['company_name'] ?? 'Unternehmen');
$companyAddressLine1 = trim((string) ($companyProfile['street'] ?? ''));
$companyAddressLine2 = trim((string) ($companyProfile['zip_code'] ?? '') . ' ' . (string) ($companyProfile['city'] ?? ''));
$companyCountry = (string) ($companyProfile['country'] ?? '');
$companyPhone = (string) ($companyProfile['phone'] ?? '');
$companyEmail = (string) ($companyProfile['email'] ?? '');
$companyWebsite = (string) ($companyProfile['website'] ?? '');
$invoiceLogoPath = (string) ($companyProfile['invoice_logo_path'] ?? '');
$companyLegalForm = (string) ($companyProfile['legal_form'] ?? '');
$companyOwnerName = (string) ($companyProfile['owner_name'] ?? '');
$companyManagingDirector = (string) ($companyProfile['managing_director'] ?? '');
$companyTaxNumber = (string) ($companyProfile['tax_number'] ?? '');
$companyVatId = (string) ($companyProfile['vat_id'] ?? '');
$companyRegistrationNumber = (string) ($companyProfile['registration_number'] ?? '');
$companyRegistrationCourt = (string) ($companyProfile['registration_court'] ?? '');
$companyShareCapital = (string) ($companyProfile['share_capital_eur'] ?? '');
$companyBankName = (string) ($companyProfile['bank_name'] ?? '');
$companyIban = (string) ($companyProfile['iban'] ?? '');
$companyBic = (string) ($companyProfile['bic'] ?? '');
$accountHolder = (string) ($companyProfile['account_holder'] ?? '');
$lineItems = is_array($document['line_items'] ?? null) ? $document['line_items'] : [];

$initialLetters = '';
foreach (preg_split('/\s+/', trim($companyName)) ?: [] as $namePart) {
    if ($namePart === '') {
        continue;
    }

    $initialLetters .= strtoupper(substr($namePart, 0, 1));
    if (strlen($initialLetters) >= 2) {
        break;
    }
}

if ($initialLetters === '') {
    $initialLetters = 'CR';
}

$customerName = (string) ($recipientProfile['name'] ?? (string) ($document['customer_name'] ?? '-'));
$customerNumber = trim((string) ($document['recipient_customer_number'] ?? ''));
$invoiceNumber = (string) ($document['number'] ?? '-');
$createdAt = (string) ($document['created_at'] ?? '-');
$dueDate = (string) ($document['due_date'] ?? '-');
$noteText = trim((string) ($document['notes'] ?? ''));
$recipientAddressLine2 = trim((string) ($recipientProfile['zip_code'] ?? '') . ' ' . (string) ($recipientProfile['city'] ?? ''));

$formatMoney = static function (mixed $value): string {
    return number_format((float) $value, 2, ',', '.');
};

$detailIntro = $noteText !== ''
    ? $noteText
    : 'Vielen Dank fuer Ihren Auftrag. Nachfolgend finden Sie die abgerechneten Positionen im Ueberblick.';

$formatFooterValue = static function (string $label, string $value): ?string {
    $trimmedValue = trim($value);

    if ($trimmedValue === '') {
        return null;
    }

    return $label . ': ' . $trimmedValue;
};

$formatIban = static function (string $iban): string {
    $compact = strtoupper(preg_replace('/\s+/', '', trim($iban)) ?? '');

    if ($compact === '') {
        return '';
    }

    $groups = str_split($compact, 4);

    return implode(' ', $groups);
};

$joinFooterParts = static function (array $parts): ?string {
    $filteredParts = array_values(array_filter($parts, static fn (mixed $part): bool => is_string($part) && trim($part) !== ''));

    if ($filteredParts === []) {
        return null;
    }

    return implode(' | ', $filteredParts);
};

$addressCompactLine = trim($companyAddressLine1 . ($companyAddressLine2 !== '' ? ', ' . $companyAddressLine2 : ''));
$addressCountryLine = $companyCountry !== '' ? $companyCountry : null;
$isSoleTraderStyle = in_array($companyLegalForm, ['Freelancer', 'Einzelunternehmen'], true);
$isGbR = $companyLegalForm === 'GbR';
$isCorporation = in_array($companyLegalForm, ['UG (haftungsbeschraenkt)', 'GmbH'], true);

$companyLabel = $isCorporation && $companyLegalForm !== ''
    ? $companyName . ', ' . $companyLegalForm
    : $companyName;

$representativeLabel = match ($companyLegalForm) {
    'GbR' => 'Gesellschafter',
    'UG (haftungsbeschraenkt)', 'GmbH' => 'Geschaeftsfuehrer',
    default => 'Inhaber',
};

$representativeLine = $isSoleTraderStyle
    ? ($companyOwnerName !== '' ? $companyOwnerName : null)
    : ($isGbR
        ? $formatFooterValue($representativeLabel, $companyOwnerName)
        : $formatFooterValue($representativeLabel, $companyManagingDirector));

$capitalLine = $isCorporation
    ? $formatFooterValue('Stammkapital', $companyShareCapital !== '' ? $companyShareCapital . ' EUR' : '')
    : null;

$footerIdentityLines = array_values(array_filter([
    $companyLabel,
    $representativeLine,
    $addressCompactLine !== '' ? $addressCompactLine : null,
    $addressCountryLine,
    $capitalLine,
]));

$footerContactLines = array_values(array_filter([
    $formatFooterValue('Tel.', $companyPhone),
    $formatFooterValue('E-Mail', $companyEmail),
    $companyWebsite !== '' ? $formatFooterValue('Web', (string) preg_replace('#^https?://#', '', $companyWebsite)) : null,
]));

$footerLegalLines = array_values(array_filter([
    $joinFooterParts([
        $formatFooterValue('Steuernr.', $companyTaxNumber),
        $formatFooterValue('USt-IdNr.', $companyVatId),
    ]),
    in_array($companyLegalForm, ['UG (haftungsbeschraenkt)', 'GmbH'], true)
        ? $joinFooterParts([
            $formatFooterValue('Amtsgericht', $companyRegistrationCourt),
            $formatFooterValue('HRB', $companyRegistrationNumber),
        ])
        : null,
]));

$footerBankLines = array_values(array_filter([
    $joinFooterParts([
        $formatFooterValue('IBAN', $formatIban($companyIban)),
        $formatFooterValue('BIC', $companyBic),
    ]),
    $joinFooterParts([
        $formatFooterValue('Kontoinhaber', $accountHolder),
        $formatFooterValue('Bank', $companyBankName),
    ]),
]));

$maxItemsFirstPage = 8;
$firstPageItems = array_slice($lineItems, 0, $maxItemsFirstPage);
$remainingItems = array_slice($lineItems, $maxItemsFirstPage);
$hasContinuation = $remainingItems !== [];

$logoDataUri = null;
if ($invoiceLogoPath !== '' && str_starts_with($invoiceLogoPath, '/assets/')) {
    $absoluteLogoPath = __DIR__ . '/../../../../public' . $invoiceLogoPath;
    if (is_file($absoluteLogoPath)) {
        $mimeType = (string) ((new \finfo(FILEINFO_MIME_TYPE))->file($absoluteLogoPath) ?: 'image/png');
        $binary = file_get_contents($absoluteLogoPath);
        if ($binary !== false) {
            $logoDataUri = 'data:' . $mimeType . ';base64,' . base64_encode($binary);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 28mm 18mm 24mm 18mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111111;
            font-size: 11px;
            line-height: 1.4;
        }

        .page {
            position: relative;
        }

        .top-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .top-grid td {
            vertical-align: top;
        }

        .brand-cell {
            width: 58%;
        }

        .meta-cell {
            width: 42%;
            text-align: right;
        }

        .logo-wrap {
            position: relative;
            width: 160px;
            height: 60px;
            margin-bottom: 16px;
        }

        .logo-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .logo-accent {
            position: absolute;
            left: 10px;
            top: 33px;
            width: 72px;
            height: 12px;
            background: #d7d151;
        }

        .logo-mark {
            position: absolute;
            left: 0;
            top: 0;
            font-size: 48px;
            line-height: 0.92;
            font-weight: bold;
            letter-spacing: -4px;
            color: #111111;
        }

        .logo-name {
            position: absolute;
            left: 2px;
            bottom: 0;
            font-size: 13px;
            font-weight: bold;
            color: #222222;
        }

        .sender-inline {
            font-size: 10px;
            color: #444444;
            margin-bottom: 18px;
        }

        .address-block,
        .company-block,
        .meta-block {
            font-size: 11px;
        }

        .company-block,
        .meta-block {
            text-align: right;
        }

        .meta-block {
            margin-top: 86px;
            line-height: 1.5;
        }

        .document-title {
            margin: 28px 0 10px;
        }

        .document-title h1 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }

        .document-subtitle {
            margin: 6px 0 0;
            font-size: 12px;
            font-weight: bold;
        }

        .salutation {
            margin: 12px 0 18px;
            font-size: 12px;
        }

        .intro-text {
            margin: 0 0 14px;
            font-style: italic;
            color: #333333;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 11px;
        }

        table.items thead th {
            background: #d9d9d9;
            padding: 4px 5px;
            text-align: left;
            font-weight: bold;
            border: 0;
        }

        table.items td {
            padding: 2px 5px;
            vertical-align: top;
            border: 0;
        }

        .col-pos { width: 8%; }
        .col-desc { width: 48%; }
        .col-qty { width: 14%; }
        .col-unit { width: 15%; }
        .col-total { width: 15%; }
        .num { text-align: right; }

        .group-label td {
            padding-top: 10px;
            font-weight: bold;
        }

        .summary-table {
            width: 42%;
            margin-left: auto;
            margin-top: 16px;
            border-collapse: collapse;
            font-size: 11px;
        }

        .summary-table td {
            padding: 3px 0;
            border: 0;
        }

        .summary-table .label {
            text-align: right;
            padding-right: 10px;
        }

        .summary-table .value {
            text-align: right;
            width: 36%;
            font-weight: bold;
        }

        .summary-separator td {
            border-top: 1px solid #333333;
            padding-top: 6px;
        }

        .summary-grand td {
            font-size: 13px;
            border-top: 1px solid #333333;
            padding-top: 6px;
        }

        .summary-muted .value {
            font-weight: normal;
        }

        .service-note {
            margin-top: 18px;
            font-size: 10px;
            color: #333333;
        }

        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -4mm;
            font-size: 8.2px;
            color: #333333;
            line-height: 1.2;
            border-top: 1px solid #d0d0d0;
            padding-top: 4px;
        }

        .footer-meta {
            margin-top: 3px;
            padding-top: 2px;
            border-top: 1px solid #e3e3e3;
            font-size: 7.2px;
            color: #666666;
        }

        .footer-meta-left {
            float: left;
            width: 70%;
            text-align: left;
        }

        .footer-meta-right {
            float: right;
            width: 30%;
            text-align: right;
        }

        .footer-meta::after {
            content: '';
            display: block;
            clear: both;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .footer-table td {
            width: 33.33%;
            vertical-align: top;
            padding-right: 10px;
        }

        .footer-heading {
            margin: 0 0 2px;
            font-size: 7.4px;
            font-weight: bold;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #666666;
        }

        .footer-line {
            margin: 0 0 1px;
            word-wrap: break-word;
        }

        .continuation-note {
            margin-top: 8px;
            font-size: 10px;
            font-style: italic;
            color: #444444;
        }

        .page-break {
            page-break-before: always;
        }

        .continuation-title {
            margin: 0 0 8px;
            font-size: 13px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="page">
        <table class="top-grid">
            <tr>
                <td class="brand-cell">
                    <div class="logo-wrap">
                        <?php if ($logoDataUri !== null): ?>
                            <img src="<?= $this->escape($logoDataUri) ?>" alt="Firmenlogo" class="logo-image">
                        <?php else: ?>
                            <div class="logo-accent"></div>
                            <div class="logo-mark"><?= $this->escape($initialLetters) ?></div>
                            <div class="logo-name"><?= $this->escape($companyName) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="sender-inline">
                        <?= $this->escape($companyName) ?>
                        <?php if ($companyAddressLine1 !== ''): ?> | <?= $this->escape($companyAddressLine1) ?><?php endif; ?>
                        <?php if ($companyAddressLine2 !== ''): ?> | <?= $this->escape($companyAddressLine2) ?><?php endif; ?>
                    </div>

                    <div class="address-block">
                        <div><?= $this->escape($customerName) ?></div>
                        <?php if ((string) ($recipientProfile['street'] ?? '') !== ''): ?><div><?= $this->escape((string) ($recipientProfile['street'] ?? '')) ?></div><?php endif; ?>
                        <?php if ($recipientAddressLine2 !== ''): ?><div><?= $this->escape($recipientAddressLine2) ?></div><?php endif; ?>
                        <?php if ((string) ($recipientProfile['country'] ?? '') !== ''): ?><div><?= $this->escape((string) ($recipientProfile['country'] ?? '')) ?></div><?php endif; ?>
                    </div>
                </td>
                <td class="meta-cell">
                    <div class="company-block">
                        <div><?= $this->escape($companyName) ?></div>
                        <?php if ($companyAddressLine1 !== ''): ?><div><?= $this->escape($companyAddressLine1) ?></div><?php endif; ?>
                        <?php if ($companyAddressLine2 !== ''): ?><div><?= $this->escape($companyAddressLine2) ?></div><?php endif; ?>
                        <?php if ($companyCountry !== ''): ?><div><?= $this->escape($companyCountry) ?></div><?php endif; ?>
                        <?php if ($companyPhone !== ''): ?><div>Tel.: <?= $this->escape($companyPhone) ?></div><?php endif; ?>
                        <?php if ($companyEmail !== ''): ?><div><?= $this->escape($companyEmail) ?></div><?php endif; ?>
                    </div>

                    <div class="meta-block">
                        <?php if ($customerNumber !== ''): ?><div>Kundennummer: <?= $this->escape($customerNumber) ?></div><?php endif; ?>
                        <div>Rechnung: <?= $this->escape($invoiceNumber) ?></div>
                        <div>Datum: <?= $this->escape($createdAt) ?></div>
                        <div>Faellig am: <?= $this->escape($dueDate) ?></div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="document-title">
            <h1>Rechnung</h1>
            <p class="document-subtitle"><?= $this->escape($companyName) ?> - <?= $this->escape($invoiceNumber) ?></p>
        </div>

        <p class="salutation">Sehr geehrte Damen und Herren,</p>
        <p class="intro-text"><?= nl2br($this->escape($detailIntro)) ?></p>

        <table class="items">
            <thead>
                <tr>
                    <th class="col-pos">Pos.</th>
                    <th class="col-desc">Bezeichnung</th>
                    <th class="col-qty num">Menge</th>
                    <th class="col-unit num">Einzelpreis</th>
                    <th class="col-total num">Gesamtpreis</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($lineItems === []): ?>
                    <tr>
                        <td>1.</td>
                        <td>Keine Positionen vorhanden</td>
                        <td class="num">-</td>
                        <td class="num">-</td>
                        <td class="num">0,00 EUR</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($firstPageItems as $index => $item): ?>
                        <tr>
                            <td><?= $this->escape((string) ($index + 1)) ?>.</td>
                            <td><?= $this->escape((string) ($item['description'] ?? '')) ?></td>
                            <td class="num"><?= $this->escape((string) ($item['quantity'] ?? '0.00')) ?></td>
                            <td class="num"><?= $this->escape($formatMoney($item['unit_price_eur'] ?? '0')) ?> EUR</td>
                            <td class="num"><?= $this->escape($formatMoney($item['line_total'] ?? '0')) ?> EUR</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($hasContinuation): ?>
            <p class="continuation-note">Weitere Positionen auf der naechsten Seite.</p>
        <?php endif; ?>

        <?php if ($hasContinuation): ?>
            <div class="page-break"></div>
            <p class="continuation-title">Rechnung <?= $this->escape($invoiceNumber) ?> - Fortfuehrung der Positionen</p>
            <table class="items">
                <thead>
                    <tr>
                        <th class="col-pos">Pos.</th>
                        <th class="col-desc">Bezeichnung</th>
                        <th class="col-qty num">Menge</th>
                        <th class="col-unit num">Einzelpreis</th>
                        <th class="col-total num">Gesamtpreis</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($remainingItems as $offset => $item): ?>
                        <tr>
                            <td><?= $this->escape((string) ($maxItemsFirstPage + $offset + 1)) ?>.</td>
                            <td><?= $this->escape((string) ($item['description'] ?? '')) ?></td>
                            <td class="num"><?= $this->escape((string) ($item['quantity'] ?? '0.00')) ?></td>
                            <td class="num"><?= $this->escape($formatMoney($item['unit_price_eur'] ?? '0')) ?> EUR</td>
                            <td class="num"><?= $this->escape($formatMoney($item['line_total'] ?? '0')) ?> EUR</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <table class="summary-table">
            <tr class="summary-separator">
                <td class="label">Summe:</td>
                <td class="value"><?= $this->escape($formatMoney($document['sub_total_eur'] ?? '0')) ?> EUR</td>
            </tr>
            <tr class="summary-muted">
                <td class="label">-<?= $this->escape((string) ($document['discount_percent'] ?? '0,00')) ?> % Rabatt:</td>
                <td class="value">-<?= $this->escape($formatMoney($document['discount_amount_eur'] ?? '0')) ?> EUR</td>
            </tr>
            <tr>
                <td class="label">Netto:</td>
                <td class="value"><?= $this->escape($formatMoney($document['net_total_eur'] ?? '0')) ?> EUR</td>
            </tr>
            <tr>
                <td class="label"><?= $this->escape((string) ($document['vat_percent'] ?? '0,00')) ?> % USt.:</td>
                <td class="value"><?= $this->escape($formatMoney($document['vat_amount_eur'] ?? '0')) ?> EUR</td>
            </tr>
            <tr class="summary-grand">
                <td class="label">Brutto:</td>
                <td class="value"><?= $this->escape($formatMoney($document['gross_total_eur'] ?? '0')) ?> EUR</td>
            </tr>
        </table>

        <div class="service-note">
            Leistungsdatum = Datum des Dokuments
        </div>

        <div class="footer">
            <table class="footer-table">
                <tr>
                    <td>
                        <p class="footer-heading">Unternehmen</p>
                        <?php foreach ($footerIdentityLines as $line): ?>
                            <div class="footer-line"><?= $this->escape($line) ?></div>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <p class="footer-heading">Kontakt</p>
                        <?php foreach ($footerContactLines as $line): ?>
                            <div class="footer-line"><?= $this->escape($line) ?></div>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <p class="footer-heading">Rechtliches und Bank</p>
                        <?php foreach ($footerLegalLines as $line): ?>
                            <div class="footer-line"><?= $this->escape($line) ?></div>
                        <?php endforeach; ?>
                        <?php foreach ($footerBankLines as $line): ?>
                            <div class="footer-line"><?= $this->escape($line) ?></div>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </table>
            <div class="footer-meta">
                <div class="footer-meta-left">Rechnung <?= $this->escape($invoiceNumber) ?> | Kunde: <?= $this->escape($customerName) ?> | Datum: <?= $this->escape($createdAt) ?></div>
                <div class="footer-meta-right"></div>
            </div>
        </div>
    </div>
    <script type="text/php">
        if (isset($pdf)) {
            $pageText = 'Seite {PAGE_NUM} von {PAGE_COUNT}';
            $font = $fontMetrics->get_font('DejaVu Sans', 'normal');
            $size = 7;
            $textWidth = $fontMetrics->get_text_width($pageText, $font, $size);
            $x = $pdf->get_width() - 52 - $textWidth;
            $y = $pdf->get_height() - 18;

            $pdf->page_text($x, $y, $pageText, $font, $size, [0.4, 0.4, 0.4]);
        }
    </script>
</body>
</html>
