<?php

declare(strict_types=1);

/** @var array<string, mixed> $company */
/** @var list<array<string, mixed>> $relatedContacts */

$companyName      = (string) ($company['company_name'] ?? '');
$legalForm        = (string) ($company['legal_form'] ?? '');
$ownerName        = (string) ($company['owner_name'] ?? '');
$managingDirector = (string) ($company['managing_director'] ?? '');
$foundedOn        = (string) ($company['founded_on'] ?? '');
$shareCapital     = (string) ($company['share_capital_eur'] ?? '');
$street           = (string) ($company['street'] ?? '');
$zipCode          = (string) ($company['zip_code'] ?? '');
$city             = (string) ($company['city'] ?? '');
$country          = (string) ($company['country'] ?? '');
$email            = (string) ($company['email'] ?? '');
$phone            = (string) ($company['phone'] ?? '');
$website          = (string) ($company['website'] ?? '');
$vatId            = (string) ($company['vat_id'] ?? '');
$taxNumber        = (string) ($company['tax_number'] ?? '');
$registrationNumber = (string) ($company['registration_number'] ?? '');
$registrationCourt  = (string) ($company['registration_court'] ?? '');

$heroDesc = array_filter([$legalForm, $city]);

$extraFieldRows = [];
foreach (($company['extra_fields'] ?? []) as $key => $field) {
    if (is_array($field) && isset($field['type'], $field['value'])) {
        $extraFieldRows[] = ['key' => (string) $key, 'type' => (string) $field['type'], 'value' => (string) $field['value']];
        continue;
    }
    $extraFieldRows[] = ['key' => (string) $key, 'type' => 'text', 'value' => (string) $field];
}

$identityItems = array_filter([
    'Rechtsform'             => $legalForm,
    'Inhaber / Gesellschafter' => $ownerName,
    'Geschaeftsfuehrer'      => $managingDirector,
    'Gruendungsdatum'        => $foundedOn,
    'Stammkapital (EUR)'     => $shareCapital,
]);

$addressItems = array_filter([
    'Strasse'  => $street,
    'PLZ'      => $zipCode,
    'Stadt'    => $city,
    'Land'     => $country,
]);

$contactItems = array_filter([
    'E-Mail'  => $email,
    'Telefon' => $phone,
    'Webseite' => $website,
]);

$complianceItems = array_filter([
    'USt-IdNr.'           => $vatId,
    'Steuernummer'        => $taxNumber,
    'Handelsregisternr.'  => $registrationNumber,
    'Registergericht'     => $registrationCourt,
]);
?>
<div class="component-stack">
    <nav class="detail-nav">
        <a href="/contacts?tab=companies" class="detail-nav-back">← Kontakte / Firmen</a>
        <a href="/contacts?edit_company=<?= $this->escape(urlencode($companyName)) ?>" class="secondary-link">Bearbeiten</a>
    </nav>

    <?= $this->component('hero', [
        'kicker'      => 'Firma',
        'title'       => $companyName,
        'description' => implode(' · ', $heroDesc) ?: 'Keine weitere Angabe',
    ]) ?>

    <section class="company-overview-strip">
        <article class="company-spotlight">
            <p class="company-spotlight-kicker">Aktiver Datensatz</p>
            <h3><?= $this->escape($companyName) ?></h3>
            <p>
                <?= $legalForm !== '' ? $this->escape($legalForm) : 'Keine Rechtsform' ?>
                <?php if ($city !== ''): ?>
                    · <?= $this->escape($city) ?>
                <?php endif; ?>
            </p>
        </article>
        <div class="company-badges">
            <?php if ($legalForm !== ''): ?>
                <span class="company-badge"><?= $this->escape($legalForm) ?></span>
            <?php endif; ?>
            <?php if ($country !== ''): ?>
                <span class="company-badge"><?= $this->escape($country) ?></span>
            <?php endif; ?>
            <?php if ($taxNumber !== '' || $vatId !== ''): ?>
                <span class="company-badge">Steuerdaten gepflegt</span>
            <?php endif; ?>
            <?php if ($registrationNumber !== ''): ?>
                <span class="company-badge">Registerdaten vorhanden</span>
            <?php endif; ?>
            <?php if (count($relatedContacts) > 0): ?>
                <span class="company-badge"><?= $this->escape((string) count($relatedContacts)) ?> Kontakt<?= count($relatedContacts) !== 1 ? 'e' : '' ?></span>
            <?php endif; ?>
        </div>
    </section>

    <div class="company-sections-grid">

        <?php if ($identityItems !== []): ?>
            <section class="company-section-card">
                <header>
                    <h3>Identitaet</h3>
                    <p>Grunddaten zur Organisation</p>
                </header>
                <div class="company-profile-grid">
                    <?php foreach ($identityItems as $label => $value): ?>
                        <div class="company-profile-item">
                            <h4><?= $this->escape($label) ?></h4>
                            <p><?= $this->escape($value) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($addressItems !== []): ?>
            <section class="company-section-card">
                <header>
                    <h3>Adresse</h3>
                    <p>Postalische Anschrift</p>
                </header>
                <div class="company-profile-grid">
                    <?php foreach ($addressItems as $label => $value): ?>
                        <div class="company-profile-item">
                            <h4><?= $this->escape($label) ?></h4>
                            <p><?= $this->escape($value) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($contactItems !== []): ?>
            <section class="company-section-card">
                <header>
                    <h3>Kontakt &amp; Web</h3>
                    <p>Erreichbarkeit und Online-Praesenzen</p>
                </header>
                <div class="company-profile-grid">
                    <?php if ($email !== ''): ?>
                        <div class="company-profile-item">
                            <h4>E-Mail</h4>
                            <p><a href="mailto:<?= $this->escape($email) ?>"><?= $this->escape($email) ?></a></p>
                        </div>
                    <?php endif; ?>
                    <?php if ($phone !== ''): ?>
                        <div class="company-profile-item">
                            <h4>Telefon</h4>
                            <p><a href="tel:<?= $this->escape($phone) ?>"><?= $this->escape($phone) ?></a></p>
                        </div>
                    <?php endif; ?>
                    <?php if ($website !== ''): ?>
                        <div class="company-profile-item">
                            <h4>Webseite</h4>
                            <p><a href="<?= $this->escape($website) ?>" target="_blank" rel="noopener noreferrer"><?= $this->escape($website) ?></a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($complianceItems !== []): ?>
            <section class="company-section-card">
                <header>
                    <h3>Steuer &amp; Register</h3>
                    <p>Fuer Rechnungen und Vertraege relevante Daten</p>
                </header>
                <div class="company-profile-grid">
                    <?php foreach ($complianceItems as $label => $value): ?>
                        <div class="company-profile-item">
                            <h4><?= $this->escape($label) ?></h4>
                            <p><?= $this->escape($value) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php
        $hasExtraData = false;
        foreach ($extraFieldRows as $row) {
            if ($row['key'] !== '' || $row['value'] !== '') {
                $hasExtraData = true;
                break;
            }
        }
        if ($hasExtraData):
        ?>
            <section class="company-section-card">
                <header>
                    <h3>Zusatzfelder</h3>
                    <p>Individuell angelegte Felder dieser Firma</p>
                </header>
                <div class="company-extra-grid">
                    <?php foreach ($extraFieldRows as $row): ?>
                        <?php if ($row['key'] === '' && $row['value'] === '') { continue; } ?>
                        <div class="company-profile-item">
                            <h4><?= $this->escape($row['key']) ?></h4>
                            <p><?= $this->escape($row['value']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="company-section-card">
            <header>
                <h3>Kontakte</h3>
                <p>Personen, die dieser Firma zugeordnet sind</p>
            </header>
            <?php if ($relatedContacts === []): ?>
                <p class="company-log-empty">Keine Kontakte dieser Firma eingetragen.</p>
            <?php else: ?>
                <div class="contact-list">
                    <?php foreach ($relatedContacts as $c): ?>
                        <?php
                        $cName = trim((string) ($c['first_name'] ?? '') . ' ' . (string) ($c['last_name'] ?? ''));
                        if ($cName === '') { $cName = '(ohne Namen)'; }
                        ?>
                        <article class="contact-item">
                            <div class="contact-item-head">
                                <h4><?= $this->escape($cName) ?></h4>
                                <?php if ((string) ($c['status'] ?? '') !== ''): ?>
                                    <span class="company-badge"><?= $this->escape((string) ($c['status'] ?? '')) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ((string) ($c['position'] ?? '') !== ''): ?>
                                <p><?= $this->escape((string) ($c['position'] ?? '')) ?></p>
                            <?php endif; ?>
                            <p class="contact-item-meta">
                                <?= (string) ($c['email'] ?? '') !== '' ? $this->escape((string) $c['email']) : '' ?>
                                <?= (string) ($c['email'] ?? '') !== '' && (string) ($c['phone'] ?? '') !== '' ? ' · ' : '' ?>
                                <?= (string) ($c['phone'] ?? '') !== '' ? $this->escape((string) $c['phone']) : '' ?>
                            </p>
                            <div class="contact-item-actions">
                                <a href="/contacts/show?id=<?= $this->escape((string) ($c['id'] ?? '')) ?>" class="secondary-link">Details</a>
                                <a href="/contacts?edit=<?= $this->escape((string) ($c['id'] ?? '')) ?>" class="secondary-link">Bearbeiten</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
</div>
