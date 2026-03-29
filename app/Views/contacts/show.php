<?php

declare(strict_types=1);

/** @var array<string, mixed> $contact */
/** @var array<string, mixed>|null $company */

$fullName = trim((string) ($contact['first_name'] ?? '') . ' ' . (string) ($contact['last_name'] ?? ''));
if ($fullName === '') {
    $fullName = '(ohne Namen)';
}

$status   = (string) ($contact['status'] ?? '');
$position = (string) ($contact['position'] ?? '');
$email    = (string) ($contact['email'] ?? '');
$phone    = (string) ($contact['phone'] ?? '');
$companyName = (string) ($contact['company'] ?? '');

$extraFieldRows = [];
foreach (($contact['extra_fields'] ?? []) as $key => $field) {
    if (is_array($field) && isset($field['type'], $field['value'])) {
        $extraFieldRows[] = ['key' => (string) $key, 'type' => (string) $field['type'], 'value' => (string) $field['value']];
        continue;
    }
    $extraFieldRows[] = ['key' => (string) $key, 'type' => 'text', 'value' => (string) $field];
}

$heroDesc = array_filter([$position, $companyName]);
?>
<div class="component-stack">
    <nav class="detail-nav">
        <a href="/contacts" class="detail-nav-back">← Kontakte</a>
        <a href="/contacts?edit=<?= $this->escape((string) ($contact['id'] ?? '')) ?>" class="secondary-link">Bearbeiten</a>
    </nav>

    <?= $this->component('hero', [
        'kicker'      => 'Kontakt',
        'title'       => $fullName,
        'description' => implode(' · ', $heroDesc) ?: 'Keine weitere Angabe',
    ]) ?>

    <section class="company-overview-strip">
        <article class="company-spotlight">
            <p class="company-spotlight-kicker">Kontaktperson</p>
            <h3><?= $this->escape($fullName) ?></h3>
            <p>
                <?= $status !== '' ? $this->escape($status) : 'Kein Status' ?>
                <?php if ($companyName !== ''): ?>
                    · <?= $this->escape($companyName) ?>
                <?php endif; ?>
            </p>
        </article>
        <div class="company-badges">
            <?php if ($status !== ''): ?>
                <span class="company-badge"><?= $this->escape($status) ?></span>
            <?php endif; ?>
            <?php if ($position !== ''): ?>
                <span class="company-badge"><?= $this->escape($position) ?></span>
            <?php endif; ?>
            <?php if ($companyName !== ''): ?>
                <span class="company-badge"><?= $this->escape($companyName) ?></span>
            <?php endif; ?>
            <?php if ($email !== ''): ?>
                <span class="company-badge">E-Mail gepflegt</span>
            <?php endif; ?>
            <?php if ($phone !== ''): ?>
                <span class="company-badge">Telefon gepflegt</span>
            <?php endif; ?>
        </div>
    </section>

    <div class="company-sections-grid">

        <section class="company-section-card">
            <header>
                <h3>Kontaktdaten</h3>
                <p>E-Mail und Telefon dieser Person</p>
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
                <?php if ($email === '' && $phone === ''): ?>
                    <p class="company-log-empty">Keine Kontaktdaten eingetragen.</p>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($companyName !== ''): ?>
            <section class="company-section-card">
                <header>
                    <h3>Firmenzugehoerigkeit</h3>
                    <p>Wo ist diese Person beschaeftigt?</p>
                </header>
                <div class="company-profile-grid">
                    <div class="company-profile-item">
                        <h4>Firma</h4>
                        <p>
                            <?php if ($company !== null): ?>
                                <a href="/contacts/companies/show?name=<?= $this->escape(urlencode((string) ($company['company_name'] ?? ''))) ?>"><?= $this->escape($companyName) ?></a>
                            <?php else: ?>
                                <?= $this->escape($companyName) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if ($position !== ''): ?>
                        <div class="company-profile-item">
                            <h4>Position</h4>
                            <p><?= $this->escape($position) ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if ($company !== null && (string) ($company['legal_form'] ?? '') !== ''): ?>
                        <div class="company-profile-item">
                            <h4>Rechtsform</h4>
                            <p><?= $this->escape((string) ($company['legal_form'] ?? '')) ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if ($company !== null && (string) ($company['city'] ?? '') !== ''): ?>
                        <div class="company-profile-item">
                            <h4>Firmenstandort</h4>
                            <p><?= $this->escape((string) ($company['city'] ?? '')) ?><?= (string) ($company['country'] ?? '') !== '' ? ', ' . $this->escape((string) $company['country']) : '' ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($extraFieldRows !== [] && !($extraFieldRows === [['key' => '', 'type' => 'text', 'value' => '']])): ?>
            <?php
            $hasExtraData = false;
            foreach ($extraFieldRows as $row) {
                if ($row['key'] !== '' || $row['value'] !== '') {
                    $hasExtraData = true;
                    break;
                }
            }
            ?>
            <?php if ($hasExtraData): ?>
                <section class="company-section-card">
                    <header>
                        <h3>Zusatzfelder</h3>
                        <p>Individuell angelegte Felder dieses Kontakts</p>
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
        <?php endif; ?>

    </div>
</div>
