<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $contacts */
/** @var int $contactsTotal */
/** @var list<string> $statuses */
/** @var list<array<string, string>> $companies */
/** @var list<string> $companyNames */
/** @var string $selectedCompany */
/** @var array<string, mixed>|null $editingContact */
/** @var array<string, string>|null $editingCompany */
/** @var string $activeTab */
/** @var string|null $success */
/** @var string|null $error */

$activeTab = in_array($activeTab, ['contacts', 'statuses', 'companies'], true) ? $activeTab : 'contacts';
$editingContact = is_array($editingContact ?? null) ? $editingContact : null;
$editingCompany = is_array($editingCompany ?? null) ? $editingCompany : null;

$editingCompanyExtraFieldRows = [];
foreach (($editingCompany['extra_fields'] ?? []) as $key => $field) {
    if (is_array($field) && isset($field['type'], $field['value'])) {
        $editingCompanyExtraFieldRows[] = ['key' => (string) $key, 'type' => (string) $field['type'], 'value' => (string) $field['value']];
        continue;
    }
    $editingCompanyExtraFieldRows[] = ['key' => (string) $key, 'type' => 'text', 'value' => (string) $field];
}
if ($editingCompanyExtraFieldRows === []) {
    $editingCompanyExtraFieldRows[] = ['key' => '', 'type' => 'text', 'value' => ''];
}

$baseContact = [
    'id' => '',
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'company' => $companyNames[0] ?? '',
    'position' => '',
    'status' => $statuses[0] ?? 'Kontakt',
    'extra_fields' => [],
];

$currentContact = $editingContact === null ? $baseContact : array_merge($baseContact, $editingContact);

$extraFieldRows = [];
foreach (($currentContact['extra_fields'] ?? []) as $key => $field) {
    if (is_array($field) && isset($field['type'], $field['value'])) {
        $extraFieldRows[] = [
            'key' => (string) $key,
            'type' => (string) $field['type'],
            'value' => (string) $field['value'],
        ];
        continue;
    }

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
?>
<div class="component-stack contacts-shell" data-contacts-shell data-initial-tab="<?= $this->escape($activeTab) ?>">
    <?= $this->component('hero', [
        'kicker' => 'CRM',
        'title' => 'Kontakte, Status und Firmen',
        'description' => 'Kontakte erfassen, Firmen zuweisen und Stammlisten fuer Status und Firmen separat verwalten.',
    ]) ?>

    <?php if ($success !== null): ?>
        <div class="auth-success flash-message" data-flash-message role="status" aria-live="polite"><?= $this->escape($success) ?></div>
    <?php endif; ?>

    <?php if ($error !== null): ?>
        <div class="auth-error flash-message" data-flash-message role="alert" aria-live="assertive"><?= $this->escape($error) ?></div>
    <?php endif; ?>

    <div class="company-mode-switch" data-contacts-switchbar>
        <button type="button" class="mode-btn <?= $activeTab === 'contacts' ? 'is-active' : '' ?>" data-contacts-switch="contacts">Kontakte</button>
        <button type="button" class="mode-btn <?= $activeTab === 'statuses' ? 'is-active' : '' ?>" data-contacts-switch="statuses">Status</button>
        <button type="button" class="mode-btn <?= $activeTab === 'companies' ? 'is-active' : '' ?>" data-contacts-switch="companies">Firmen</button>
    </div>

    <section class="contacts-panel <?= $activeTab === 'contacts' ? '' : 'is-hidden' ?>" data-contacts-tab-panel="contacts">
        <section class="company-section-card">
            <header>
                <h3>Kontaktuebersicht</h3>
                <p>Zu Beginn werden alle Kontakte angezeigt und koennen direkt durchsucht werden.</p>
            </header>

            <div class="contact-toolbar">
                <div class="contact-search-bar">
                    <input id="contact_search" type="search" placeholder="Name, E-Mail, Telefon, Firma oder Status ..." data-contact-search>
                    <p class="contact-search-meta" data-contact-search-meta><?= $this->escape((string) count($contacts)) ?> von <?= $this->escape((string) $contactsTotal) ?> sichtbar</p>
                </div>
                <button type="button" class="add-contact-btn" data-open-contact-dialog>+ Kontakt anlegen</button>
            </div>

            <form method="get" action="/contacts" class="contact-filter-form">
                <div class="form-field">
                    <label for="company_filter">Nach Firma filtern</label>
                    <select id="company_filter" name="company" class="status-select">
                        <option value="">Alle Firmen</option>
                        <?php foreach ($companyNames as $company): ?>
                            <option value="<?= $this->escape($company) ?>" <?= $selectedCompany === $company ? 'selected' : '' ?>><?= $this->escape($company) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="profile-actions">
                    <button type="submit">Filter anwenden</button>
                    <?php if ($selectedCompany !== ''): ?>
                        <a href="/contacts" class="secondary-link">Filter zuruecksetzen</a>
                    <?php endif; ?>
                </div>
            </form>

            <?php if ($contacts === []): ?>
                <p class="company-log-empty">Noch keine Kontakte angelegt.</p>
            <?php else: ?>
                <div class="contact-list" data-contact-list>
                        <?php foreach ($contacts as $contact): ?>
                            <?php
                            $fullName = trim((string) (($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')));
                            if ($fullName === '') {
                                $fullName = '(ohne Namen)';
                            }

                            $searchText = implode(' ', [
                                $fullName,
                                (string) ($contact['email'] ?? ''),
                                (string) ($contact['phone'] ?? ''),
                                (string) ($contact['company'] ?? ''),
                                (string) ($contact['status'] ?? ''),
                                (string) ($contact['position'] ?? ''),
                            ]);
                            ?>
                    <article class="contact-item" data-contact-entry data-search-text="<?= $this->escape(strtolower($searchText)) ?>">
                        <div class="contact-item-head">
                            <h4><?= $this->escape($fullName) ?></h4>
                            <span class="company-badge"><?= $this->escape((string) ($contact['status'] ?? 'Kontakt')) ?></span>
                        </div>
                        <p><?= $this->escape((string) ($contact['company'] ?? '')) ?></p>
                        <p class="contact-item-meta"><?= $this->escape((string) ($contact['email'] ?? '')) ?><?= (string) ($contact['phone'] ?? '') !== '' ? ' · ' . $this->escape((string) $contact['phone']) : '' ?></p>

                        <div class="contact-item-actions">
                            <a href="/contacts/show?id=<?= $this->escape((string) ($contact['id'] ?? '')) ?>" class="secondary-link">Details</a>
                            <a href="/contacts?edit=<?= $this->escape((string) ($contact['id'] ?? '')) ?>" class="secondary-link">Bearbeiten</a>
                            <form method="post" action="/contacts/delete">
                                <input type="hidden" name="id" value="<?= $this->escape((string) ($contact['id'] ?? '')) ?>">
                                <button type="submit" class="mini-danger">Loeschen</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
                </div>
                <p class="company-log-empty is-hidden" data-contact-search-empty>Keine Kontakte passend zur Suche gefunden.</p>
            <?php endif; ?>
        </section>

        <dialog class="contact-dialog" data-contact-dialog data-contact-dialog-auto-open="<?= $editingContact !== null ? '1' : '0' ?>">
            <div class="contact-dialog-head">
                <h3><?= $editingContact === null ? 'Neuen Kontakt anlegen' : 'Kontakt bearbeiten' ?></h3>
                <button type="button" class="secondary-btn" data-close-contact-dialog>Schliessen</button>
            </div>

            <form method="post" action="/contacts" class="profile-form contact-dialog-form">
                <input type="hidden" name="id" value="<?= $this->escape((string) ($currentContact['id'] ?? '')) ?>">

                <div class="profile-grid">
                    <div class="form-field"><label for="first_name">Vorname</label><input id="first_name" name="first_name" value="<?= $this->escape((string) ($currentContact['first_name'] ?? '')) ?>"></div>
                    <div class="form-field"><label for="last_name">Nachname</label><input id="last_name" name="last_name" value="<?= $this->escape((string) ($currentContact['last_name'] ?? '')) ?>"></div>
                    <div class="form-field"><label for="email">E-Mail</label><input id="email" name="email" type="email" value="<?= $this->escape((string) ($currentContact['email'] ?? '')) ?>"></div>
                    <div class="form-field"><label for="phone">Telefon</label><input id="phone" name="phone" value="<?= $this->escape((string) ($currentContact['phone'] ?? '')) ?>"></div>
                    <div class="form-field">
                        <label for="company">Firma</label>
                        <select id="company" name="company" class="status-select">
                            <option value="">(keine Firma)</option>
                            <?php foreach ($companyNames as $company): ?>
                                <option value="<?= $this->escape($company) ?>" <?= (string) ($currentContact['company'] ?? '') === $company ? 'selected' : '' ?>><?= $this->escape($company) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field"><label for="position">Rolle / Position</label><input id="position" name="position" value="<?= $this->escape((string) ($currentContact['position'] ?? '')) ?>"></div>
                    <div class="form-field">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="status-select">
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= $this->escape($status) ?>" <?= (string) ($currentContact['status'] ?? '') === $status ? 'selected' : '' ?>><?= $this->escape($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-field">
                    <label>Zusatzfelder (erweiterbar)</label>
                    <div class="kv-module" data-kv-module>
                        <div class="kv-rows" data-kv-rows>
                            <?php foreach ($extraFieldRows as $row): ?>
                                <div class="kv-row" data-kv-row>
                                    <input type="text" name="extra_field_key[]" placeholder="key (z. B. xing)" value="<?= $this->escape($row['key']) ?>">
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
                    <?php if ($editingContact !== null): ?>
                        <a href="/contacts" class="secondary-link">Bearbeitung verlassen</a>
                    <?php endif; ?>
                </div>
            </form>
        </dialog>
    </section>

    <section class="contacts-panel <?= $activeTab === 'statuses' ? '' : 'is-hidden' ?>" data-contacts-tab-panel="statuses">
        <section class="company-section-card">
            <header>
                <h3>Status</h3>
                <p>Definiere deine eigenen Kontakt-Status fuer Lead-Pipeline, Kunden und Teamrollen.</p>
            </header>

            <div class="contact-toolbar">
                <div></div>
                <button type="button" class="add-contact-btn" data-open-status-dialog>+ Status anlegen</button>
            </div>

            <div class="status-list">
                <?php foreach ($statuses as $status): ?>
                    <article class="status-item">
                        <p class="status-item-name"><?= $this->escape($status) ?></p>
                        <form method="post" action="/contacts/statuses/delete" class="status-item-delete">
                            <input type="hidden" name="status_name" value="<?= $this->escape($status) ?>">
                            <button type="submit" class="mini-danger">Entfernen</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <dialog class="contact-dialog" data-status-dialog>
            <div class="contact-dialog-head">
                <h3>Status anlegen</h3>
                <button type="button" class="secondary-btn" data-close-status-dialog>Schliessen</button>
            </div>
            <form method="post" action="/contacts/statuses/add" class="profile-form">
                <div class="form-field">
                    <label for="new_status_name">Statusname</label>
                    <input id="new_status_name" name="status_name" placeholder="z. B. Interessent" required>
                </div>
                <div class="profile-actions">
                    <button type="submit">Speichern</button>
                </div>
            </form>
        </dialog>
    </section>

    <section class="contacts-panel <?= $activeTab === 'companies' ? '' : 'is-hidden' ?>" data-contacts-tab-panel="companies">
        <section class="company-section-card">
            <header>
                <h3>Firmen</h3>
                <p>Lege abrechnungsfaehige Firmendaten inklusive Kontakt und Webseite separat an.</p>
            </header>

            <div class="contact-toolbar">
                <div></div>
                <button type="button" class="add-contact-btn" data-open-company-dialog>+ Firma anlegen</button>
            </div>

            <?php if ($companies === []): ?>
                <p class="company-log-empty">Noch keine Firmen angelegt.</p>
            <?php else: ?>
                <div class="contact-list">
                    <?php foreach ($companies as $company): ?>
                        <?php
                        $companyAddress = implode(', ', array_filter([
                            (string) ($company['street'] ?? ''),
                            trim((string) ($company['zip_code'] ?? '') . ' ' . (string) ($company['city'] ?? '')),
                            (string) ($company['country'] ?? ''),
                        ]));
                        ?>
                        <article class="contact-item">
                            <div class="contact-item-head">
                                <h4><?= $this->escape((string) ($company['company_name'] ?? '')) ?></h4>
                                <?php if ((string) ($company['legal_form'] ?? '') !== ''): ?>
                                    <span class="company-badge"><?= $this->escape((string) ($company['legal_form'] ?? '')) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($companyAddress !== ''): ?>
                                <p><?= $this->escape($companyAddress) ?></p>
                            <?php endif; ?>
                            <?php if ((string) ($company['website'] ?? '') !== ''): ?>
                                <p class="contact-item-meta"><?= $this->escape((string) ($company['website'] ?? '')) ?></p>
                            <?php endif; ?>
                            <div class="contact-item-actions">
                                <a href="/contacts/companies/show?name=<?= $this->escape(urlencode((string) ($company['company_name'] ?? ''))) ?>" class="secondary-link">Details</a>
                                <a href="/contacts?edit_company=<?= $this->escape(urlencode((string) ($company['company_name'] ?? ''))) ?>" class="secondary-link">Bearbeiten</a>
                                <form method="post" action="/contacts/companies/delete">
                                    <input type="hidden" name="company_name" value="<?= $this->escape((string) ($company['company_name'] ?? '')) ?>">
                                    <button type="submit" class="mini-danger">Loeschen</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <dialog class="contact-dialog" data-company-dialog data-company-dialog-auto-open="<?= $editingCompany !== null ? '1' : '0' ?>">
            <div class="contact-dialog-head">
                <h3><?= $editingCompany === null ? 'Firma anlegen' : 'Firma bearbeiten' ?></h3>
                <button type="button" class="secondary-btn" data-close-company-dialog>Schliessen</button>
            </div>
            <form method="post" action="/contacts/companies/save" class="profile-form contact-dialog-form">
                <input type="hidden" name="original_name" value="<?= $this->escape((string) ($editingCompany['company_name'] ?? '')) ?>">

                <?php
                $cdfLf = (string) ($editingCompany['legal_form'] ?? '');
                ?>
                <div class="form-field">
                    <label for="cdf_legal_form">Rechtsform</label>
                    <select id="cdf_legal_form" name="legal_form" class="status-select" data-company-dialog-legal-form>
                        <option value="" <?= $cdfLf === '' ? 'selected' : '' ?>>Keine Angabe</option>
                        <option value="Freelancer" <?= $cdfLf === 'Freelancer' ? 'selected' : '' ?>>Freelancer</option>
                        <option value="Einzelunternehmen" <?= $cdfLf === 'Einzelunternehmen' ? 'selected' : '' ?>>Einzelunternehmen</option>
                        <option value="GbR" <?= $cdfLf === 'GbR' ? 'selected' : '' ?>>GbR</option>
                        <option value="UG (haftungsbeschraenkt)" <?= $cdfLf === 'UG (haftungsbeschraenkt)' ? 'selected' : '' ?>>UG (haftungsbeschraenkt)</option>
                        <option value="GmbH" <?= $cdfLf === 'GmbH' ? 'selected' : '' ?>>GmbH</option>
                    </select>
                    <small class="field-help" data-cdf-lf-help></small>
                </div>

                <p class="company-master-section-title">Stammdaten</p>
                <div class="profile-grid">
                    <div class="form-field" data-cdf-role="company_name">
                        <label for="cdf_company_name">Firmenname</label>
                        <input id="cdf_company_name" name="company_name" value="<?= $this->escape((string) ($editingCompany['company_name'] ?? '')) ?>" required>
                    </div>
                    <div class="form-field" data-cdf-role="owner_name">
                        <label for="cdf_owner_name" data-cdf-label="owner_name">Inhaber / Gesellschafter</label>
                        <input id="cdf_owner_name" name="owner_name" value="<?= $this->escape((string) ($editingCompany['owner_name'] ?? '')) ?>">
                    </div>
                    <div class="form-field" data-cdf-role="managing_director">
                        <label for="cdf_managing_director" data-cdf-label="managing_director">Geschaeftsfuehrer</label>
                        <input id="cdf_managing_director" name="managing_director" value="<?= $this->escape((string) ($editingCompany['managing_director'] ?? '')) ?>">
                    </div>
                    <div class="form-field" data-cdf-role="founded_on">
                        <label for="cdf_founded_on">Gruendungsdatum</label>
                        <input id="cdf_founded_on" name="founded_on" type="date" value="<?= $this->escape((string) ($editingCompany['founded_on'] ?? '')) ?>">
                    </div>
                </div>

                <p class="company-master-section-title">Adresse</p>
                <div class="profile-grid">
                    <div class="form-field"><label for="cdf_street">Strasse</label><input id="cdf_street" name="street" value="<?= $this->escape((string) ($editingCompany['street'] ?? '')) ?>"></div>
                    <div class="form-field"><label for="cdf_zip_code">PLZ</label><input id="cdf_zip_code" name="zip_code" value="<?= $this->escape((string) ($editingCompany['zip_code'] ?? '')) ?>"></div>
                    <div class="form-field"><label for="cdf_city">Stadt</label><input id="cdf_city" name="city" value="<?= $this->escape((string) ($editingCompany['city'] ?? '')) ?>"></div>
                    <div class="form-field"><label for="cdf_country">Land</label><input id="cdf_country" name="country" value="<?= $this->escape((string) ($editingCompany['country'] ?? '')) ?>"></div>
                </div>

                <p class="company-master-section-title">Kontakt &amp; Web</p>
                <div class="profile-grid">
                    <div class="form-field"><label for="cdf_email">E-Mail</label><input id="cdf_email" name="email" type="email" value="<?= $this->escape((string) ($editingCompany['email'] ?? '')) ?>"></div>
                    <div class="form-field"><label for="cdf_phone">Telefon</label><input id="cdf_phone" name="phone" value="<?= $this->escape((string) ($editingCompany['phone'] ?? '')) ?>"></div>
                    <div class="form-field"><label for="cdf_website">Webseite</label><input id="cdf_website" name="website" type="url" value="<?= $this->escape((string) ($editingCompany['website'] ?? '')) ?>" placeholder="https://..."></div>
                </div>

                <p class="company-master-section-title">Steuer &amp; Register</p>
                <div class="profile-grid">
                    <div class="form-field"><label for="cdf_vat_id">USt-IdNr.</label><input id="cdf_vat_id" name="vat_id" value="<?= $this->escape((string) ($editingCompany['vat_id'] ?? '')) ?>"></div>
                    <div class="form-field"><label for="cdf_tax_number">Steuernummer</label><input id="cdf_tax_number" name="tax_number" value="<?= $this->escape((string) ($editingCompany['tax_number'] ?? '')) ?>"></div>
                    <div class="form-field" data-cdf-role="registration_number"><label for="cdf_registration_number">Handelsregisternr.</label><input id="cdf_registration_number" name="registration_number" value="<?= $this->escape((string) ($editingCompany['registration_number'] ?? '')) ?>"></div>
                    <div class="form-field" data-cdf-role="registration_court"><label for="cdf_registration_court">Registergericht</label><input id="cdf_registration_court" name="registration_court" value="<?= $this->escape((string) ($editingCompany['registration_court'] ?? '')) ?>"></div>
                    <div class="form-field" data-cdf-role="share_capital_eur"><label for="cdf_share_capital_eur">Stammkapital (EUR)</label><input id="cdf_share_capital_eur" name="share_capital_eur" value="<?= $this->escape((string) ($editingCompany['share_capital_eur'] ?? '')) ?>"></div>
                </div>

                <p class="company-master-section-title">Zusatzfelder</p>
                <div class="kv-module" data-cdf-kv-module>
                    <div class="kv-rows" data-cdf-kv-rows>
                        <?php foreach ($editingCompanyExtraFieldRows as $row): ?>
                            <div class="kv-row" data-cdf-kv-row>
                                <input type="text" name="extra_field_key[]" placeholder="key (z. B. iban)" value="<?= $this->escape($row['key']) ?>">
                                <input type="text" name="extra_field_value[]" placeholder="value" value="<?= $this->escape($row['value']) ?>">
                                <select name="extra_field_type[]">
                                    <option value="text" <?= $row['type'] === 'text' ? 'selected' : '' ?>>text</option>
                                    <option value="number" <?= $row['type'] === 'number' ? 'selected' : '' ?>>number</option>
                                    <option value="boolean" <?= $row['type'] === 'boolean' ? 'selected' : '' ?>>boolean</option>
                                    <option value="date" <?= $row['type'] === 'date' ? 'selected' : '' ?>>date</option>
                                </select>
                                <button type="button" class="kv-remove" data-cdf-kv-remove>Entfernen</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="kv-add" data-cdf-kv-add>+ Feld hinzufuegen</button>
                </div>

                <div class="profile-actions">
                    <button type="submit">Speichern</button>
                    <?php if ($editingCompany !== null): ?>
                        <a href="/contacts" class="secondary-link">Bearbeitung verlassen</a>
                    <?php endif; ?>
                </div>
            </form>
        </dialog>
    </section>
</div>
