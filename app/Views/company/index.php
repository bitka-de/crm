<?php

declare(strict_types=1);

/** @var array<string, mixed> $profile */
/** @var array<int, array<string, mixed>> $changeLog */
/** @var string|null $success */
/** @var string|null $error */

$legalForm = (string) ($profile['legal_form'] ?? 'UG (haftungsbeschraenkt)');
$isEditMode = $error !== null;

$extraFieldRows = [];

foreach (($profile['extra_fields'] ?? []) as $key => $field) {
    if (is_array($field) && isset($field['type'], $field['value'])) {
        $extraFieldRows[] = [
            'key' => (string) $key,
            'type' => (string) $field['type'],
            'value' => (string) $field['value'],
        ];
        continue;
    }

    // Rueckwaertskompatibel fuer alte, untypisierte Struktur.
    $extraFieldRows[] = [
        'key' => (string) $key,
        'type' => 'text',
        'value' => (string) $field,
    ];
}

if ($extraFieldRows === []) {
    $extraFieldRows[] = [
        'key' => '',
        'type' => 'text',
        'value' => '',
    ];
}

$identityItems = [
    'Unternehmensname' => (string) ($profile['company_name'] ?? ''),
    'Rechtsform' => (string) ($profile['legal_form'] ?? ''),
    'Gesellschafter / Inhaber' => (string) ($profile['owner_name'] ?? ''),
    'Geschaeftsfuehrer' => (string) ($profile['managing_director'] ?? ''),
    'Gruendungsdatum' => (string) ($profile['founded_on'] ?? ''),
    'Stammkapital (EUR)' => (string) ($profile['share_capital_eur'] ?? ''),
];

$contactItems = [
    'Strasse' => (string) ($profile['street'] ?? ''),
    'PLZ' => (string) ($profile['zip_code'] ?? ''),
    'Stadt' => (string) ($profile['city'] ?? ''),
    'Land' => (string) ($profile['country'] ?? ''),
    'E-Mail' => (string) ($profile['email'] ?? ''),
    'Telefon' => (string) ($profile['phone'] ?? ''),
    'Webseite' => (string) ($profile['website'] ?? ''),
];

$complianceItems = [
    'USt-IdNr.' => (string) ($profile['vat_id'] ?? ''),
    'Steuernummer' => (string) ($profile['tax_number'] ?? ''),
    'Handelsregisternummer' => (string) ($profile['registration_number'] ?? ''),
    'Registergericht' => (string) ($profile['registration_court'] ?? ''),
];

$changeLog = is_array($changeLog ?? null) ? $changeLog : [];
?>
<div class="component-stack company-shell <?= $isEditMode ? 'is-edit' : 'is-view' ?>" data-company-shell>
    <?= $this->component('hero', [
        'kicker' => 'Stammdaten',
        'title' => 'Unternehmensdaten (Deutschland)',
        'description' => 'Die Felder passen sich an Freelancer, Einzelunternehmen, GbR, UG und GmbH an und bleiben zentral erweiterbar.',
    ]) ?>

    <?php if ($success !== null): ?>
        <div class="auth-success flash-message" data-flash-message role="status" aria-live="polite"><?= $this->escape($success) ?></div>
    <?php endif; ?>

    <?php if ($error !== null): ?>
        <div class="auth-error flash-message" data-flash-message role="alert" aria-live="assertive"><?= $this->escape($error) ?></div>
    <?php endif; ?>

    <div class="company-mode-switch" data-company-mode-switch>
        <button type="button" class="mode-btn <?= $isEditMode ? '' : 'is-active' ?>" data-company-switch="view">Ansicht</button>
        <button type="button" class="mode-btn <?= $isEditMode ? 'is-active' : '' ?>" data-company-switch="edit">Editieren</button>
        <button type="button" class="mode-btn" data-company-switch="log">Aenderungsprotokoll</button>
    </div>

    <section class="company-profile-view <?= $isEditMode ? 'is-hidden' : '' ?>" data-company-view>
        <div class="company-profile-header">
            <h2 class="panel-title">Gespeicherte Unternehmensdaten</h2>
        </div>

        <section class="company-overview-strip">
            <article class="company-spotlight">
                <p class="company-spotlight-kicker">Aktiver Datensatz</p>
                <h3><?= $this->escape((string) ($profile['company_name'] ?? 'Unternehmen')) ?></h3>
                <p>
                    <?= $this->escape((string) ($profile['legal_form'] ?? '-')) ?>
                    in <?= $this->escape((string) (($profile['city'] ?? '') !== '' ? $profile['city'] : 'Deutschland')) ?>
                </p>
            </article>
            <div class="company-badges">
                <span class="company-badge"><?= $this->escape((string) ($profile['country'] ?? 'Deutschland')) ?></span>
                <?php if ((string) ($profile['tax_number'] ?? '') !== ''): ?>
                    <span class="company-badge">Steuernummer gepflegt</span>
                <?php endif; ?>
                <?php if ((string) ($profile['registration_number'] ?? '') !== ''): ?>
                    <span class="company-badge">Registerdaten vorhanden</span>
                <?php endif; ?>
            </div>
        </section>

        <div class="company-sections-grid">
            <section class="company-section-card">
                <header>
                    <h3>Identitaet</h3>
                    <p>Grunddaten zur Organisation</p>
                </header>
                <div class="company-profile-grid">
                    <?php foreach ($identityItems as $label => $value): ?>
                        <?php if ($value === '') { continue; } ?>
                        <article class="company-profile-item">
                            <h4><?= $this->escape($label) ?></h4>
                            <p><?= $this->escape($value) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="company-section-card">
                <header>
                    <h3>Kontakt</h3>
                    <p>Erreichbarkeit und Adresse</p>
                </header>
                <div class="company-profile-grid">
                    <?php foreach ($contactItems as $label => $value): ?>
                        <?php if ($value === '') { continue; } ?>
                        <article class="company-profile-item">
                            <h4><?= $this->escape($label) ?></h4>
                            <p><?= $this->escape($value) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="company-section-card">
                <header>
                    <h3>Rechtliches</h3>
                    <p>Steuern und Register</p>
                </header>
                <div class="company-profile-grid">
                    <?php foreach ($complianceItems as $label => $value): ?>
                        <?php if ($value === '') { continue; } ?>
                        <article class="company-profile-item">
                            <h4><?= $this->escape($label) ?></h4>
                            <p><?= $this->escape($value) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <?php if ($extraFieldRows !== []): ?>
            <div class="company-extra-list">
                <h3>Zusatzfelder</h3>
                <div class="company-extra-grid">
                    <?php foreach ($extraFieldRows as $row): ?>
                        <?php if ($row['key'] === '' && $row['value'] === '') { continue; } ?>
                        <article class="company-extra-item">
                            <p class="company-extra-key"><?= $this->escape($row['key']) ?></p>
                            <p class="company-extra-value"><?= $this->escape($row['value']) ?></p>
                            <p class="company-extra-type"><?= $this->escape($row['type']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </section>

    <section class="company-section-card is-hidden" data-company-log>
        <header>
            <h3>Aenderungsprotokoll</h3>
            <p>Neueste Aenderung oben: Was wurde wann von wem geaendert</p>
        </header>

        <?php if ($changeLog === []): ?>
            <p class="company-log-empty">Noch keine Aenderungen protokolliert.</p>
        <?php else: ?>
            <div class="company-log-list">
                <?php foreach ($changeLog as $entry): ?>
                    <?php
                    $changedAtRaw = (string) ($entry['changed_at'] ?? '');
                    $changedBy = (string) ($entry['changed_by'] ?? 'unbekannt');
                    $changes = is_array($entry['changes'] ?? null) ? $entry['changes'] : [];
                    $changedAtLabel = $changedAtRaw;
                    try {
                        if ($changedAtRaw !== '') {
                            $changedAtLabel = (new \DateTimeImmutable($changedAtRaw))->format('d.m.Y H:i');
                        }
                    } catch (\Exception) {
                        $changedAtLabel = $changedAtRaw;
                    }
                    ?>
                    <article class="company-log-item">
                        <p class="company-log-meta">
                            <strong><?= $this->escape($changedBy) ?></strong>
                            <span><?= $this->escape($changedAtLabel) ?></span>
                        </p>

                        <?php if ($changes === []): ?>
                            <p class="company-log-empty">Keine Feldaenderungen hinterlegt.</p>
                        <?php else: ?>
                            <ul class="company-log-changes">
                                <?php foreach ($changes as $change): ?>
                                    <?php
                                    $field = (string) ($change['field'] ?? 'Feld');
                                    $from = (string) ($change['from'] ?? '');
                                    $to = (string) ($change['to'] ?? '');
                                    ?>
                                    <li>
                                        <strong><?= $this->escape($field) ?>:</strong>
                                        <span class="company-log-from"><?= $this->escape($from === '' ? '(leer)' : $from) ?></span>
                                        <span class="company-log-arrow">-></span>
                                        <span class="company-log-to"><?= $this->escape($to === '' ? '(leer)' : $to) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <form method="post" action="/company" class="profile-form <?= $isEditMode ? '' : 'is-hidden' ?>" data-company-edit>
        <div class="form-field legal-form-hint">
            <span class="field-label">Rechtsform</span>
            <div class="legal-form-group" role="radiogroup" aria-label="Rechtsform">
                <label class="legal-form-option">
                    <input type="radio" name="legal_form" value="Freelancer" data-legal-form-radio <?= $legalForm === 'Freelancer' ? 'checked' : '' ?>>
                    <span>Freelancer</span>
                </label>
                <label class="legal-form-option">
                    <input type="radio" name="legal_form" value="Einzelunternehmen" data-legal-form-radio <?= $legalForm === 'Einzelunternehmen' ? 'checked' : '' ?>>
                    <span>Einzelunternehmen</span>
                </label>
                <label class="legal-form-option">
                    <input type="radio" name="legal_form" value="GbR" data-legal-form-radio <?= $legalForm === 'GbR' ? 'checked' : '' ?>>
                    <span>GbR</span>
                </label>
                <label class="legal-form-option">
                    <input type="radio" name="legal_form" value="UG (haftungsbeschraenkt)" data-legal-form-radio <?= $legalForm === 'UG (haftungsbeschraenkt)' ? 'checked' : '' ?>>
                    <span>UG (haftungsbeschraenkt)</span>
                </label>
                <label class="legal-form-option">
                    <input type="radio" name="legal_form" value="GmbH" data-legal-form-radio <?= $legalForm === 'GmbH' ? 'checked' : '' ?>>
                    <span>GmbH</span>
                </label>
            </div>
            <small class="field-help" data-legal-form-help></small>
        </div>

        <div class="profile-grid">
            <div class="form-field" data-role="company_name">
                <label for="company_name" data-label-company>Unternehmensname</label>
                <input id="company_name" name="company_name" value="<?= $this->escape((string) ($profile['company_name'] ?? '')) ?>" required>
            </div>
            <div class="form-field" data-role="owner_name">
                <label for="owner_name" data-label-owner>Gesellschafter / Inhaber</label>
                <input id="owner_name" name="owner_name" value="<?= $this->escape((string) ($profile['owner_name'] ?? '')) ?>">
            </div>
            <div class="form-field" data-role="managing_director">
                <label for="managing_director" data-label-director>Geschaeftsfuehrer</label>
                <input id="managing_director" name="managing_director" value="<?= $this->escape((string) ($profile['managing_director'] ?? '')) ?>">
            </div>
            <div class="form-field" data-role="street"><label for="street">Strasse</label><input id="street" name="street" value="<?= $this->escape((string) ($profile['street'] ?? '')) ?>"></div>
            <div class="form-field" data-role="zip_code"><label for="zip_code">PLZ</label><input id="zip_code" name="zip_code" value="<?= $this->escape((string) ($profile['zip_code'] ?? '')) ?>"></div>
            <div class="form-field" data-role="city"><label for="city">Stadt</label><input id="city" name="city" value="<?= $this->escape((string) ($profile['city'] ?? '')) ?>"></div>
            <div class="form-field" data-role="country"><label for="country">Land</label><input id="country" name="country" value="<?= $this->escape((string) ($profile['country'] ?? '')) ?>"></div>
            <div class="form-field" data-role="email"><label for="email">E-Mail</label><input id="email" name="email" type="email" value="<?= $this->escape((string) ($profile['email'] ?? '')) ?>"></div>
            <div class="form-field" data-role="phone"><label for="phone">Telefon</label><input id="phone" name="phone" value="<?= $this->escape((string) ($profile['phone'] ?? '')) ?>"></div>
            <div class="form-field" data-role="website"><label for="website">Webseite</label><input id="website" name="website" value="<?= $this->escape((string) ($profile['website'] ?? '')) ?>"></div>
            <div class="form-field" data-role="vat_id"><label for="vat_id">USt-IdNr.</label><input id="vat_id" name="vat_id" value="<?= $this->escape((string) ($profile['vat_id'] ?? '')) ?>"></div>
            <div class="form-field" data-role="tax_number"><label for="tax_number">Steuernummer</label><input id="tax_number" name="tax_number" value="<?= $this->escape((string) ($profile['tax_number'] ?? '')) ?>"></div>
            <div class="form-field" data-role="registration_number"><label for="registration_number">Handelsregisternummer</label><input id="registration_number" name="registration_number" value="<?= $this->escape((string) ($profile['registration_number'] ?? '')) ?>"></div>
            <div class="form-field" data-role="registration_court"><label for="registration_court">Registergericht</label><input id="registration_court" name="registration_court" value="<?= $this->escape((string) ($profile['registration_court'] ?? '')) ?>"></div>
            <div class="form-field" data-role="share_capital_eur"><label for="share_capital_eur">Stammkapital (EUR)</label><input id="share_capital_eur" name="share_capital_eur" value="<?= $this->escape((string) ($profile['share_capital_eur'] ?? '')) ?>"></div>
            <div class="form-field" data-role="founded_on"><label for="founded_on">Gruendungsdatum</label><input id="founded_on" name="founded_on" type="date" value="<?= $this->escape((string) ($profile['founded_on'] ?? '')) ?>"></div>
        </div>

        <div class="form-field">
            <label>Zusatzfelder (erweiterbar)</label>
            <div class="kv-module" data-kv-module>
                <div class="kv-rows" data-kv-rows>
                    <?php foreach ($extraFieldRows as $row): ?>
                        <div class="kv-row" data-kv-row>
                            <input type="text" name="extra_field_key[]" placeholder="key (z. B. iban)" value="<?= $this->escape($row['key']) ?>">
                            <input type="text" name="extra_field_value[]" placeholder="value" value="<?= $this->escape($row['value']) ?>">
                            <select name="extra_field_type[]">
                                <option value="text" <?= $row['type'] === 'text' ? 'selected' : '' ?>>text</option>
                                <option value="number" <?= $row['type'] === 'number' ? 'selected' : '' ?>>number</option>
                                <option value="boolean" <?= $row['type'] === 'boolean' ? 'selected' : '' ?>>boolean</option>
                                <option value="date" <?= $row['type'] === 'date' ? 'selected' : '' ?>>date</option>
                            </select>
                            <button type="button" class="kv-remove" data-kv-remove>Entfernen</button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="kv-add" data-kv-add>+ Feld hinzufuegen</button>
            </div>
        </div>

        <div class="profile-actions">
            <button type="submit">Speichern</button>
            <a href="/dashboard" class="secondary-link">Zurueck zum Dashboard</a>
        </div>
    </form>
</div>
