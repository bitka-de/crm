<?php

declare(strict_types=1);

/** @var list<array<string, string>> $offers */
/** @var list<array<string, mixed>> $invoices */
/** @var list<array<string, string>> $reminders */
/** @var list<string> $customerOptions */
/** @var string|null $success */
/** @var string|null $error */
?>
<div class="component-stack">
  <?= $this->component('hero', [
    'kicker' => 'Dokumente',
    'title' => 'Angebote, Rechnungen und Mahnungen',
    'description' => 'Hier kannst du alle abrechnungsrelevanten Dokumente zentral erstellen und verwalten.',
  ]) ?>

  <?php if ($success !== null): ?>
    <div class="auth-success flash-message" data-flash-message role="status" aria-live="polite"><?= $this->escape($success) ?></div>
  <?php endif; ?>

  <?php if ($error !== null): ?>
    <div class="auth-error flash-message" data-flash-message role="alert" aria-live="assertive"><?= $this->escape($error) ?></div>
  <?php endif; ?>

  <div class="documents-tabs" data-documents-shell>
    <nav class="documents-tabs-nav">
      <button class="documents-tab-button is-active" data-documents-switch="offers" type="button">Angebote</button>
      <button class="documents-tab-button" data-documents-switch="invoices" type="button">Rechnungen</button>
      <button class="documents-tab-button" data-documents-switch="reminders" type="button">Mahnungen</button>
    </nav>

    <section class="company-section-card" data-documents-view="offers">
      <header>
        <h3>Angebot erstellen</h3>
        <p>Neues Angebot fuer einen Kunden anlegen.</p>
      </header>
      <form method="post" action="/documents/offers" class="profile-form">
        <div class="profile-grid">
          <div class="form-field">
            <label for="offer_customer">Kunde</label>
            <select id="offer_customer" name="customer_name" class="status-select" required>
              <option value="">Bitte auswaehlen</option>
              <?php foreach ($customerOptions as $customerOption): ?>
                <option value="<?= $this->escape($customerOption) ?>"><?= $this->escape($customerOption) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field"><label for="offer_amount">Betrag (EUR)</label><input id="offer_amount" name="amount_eur" type="number" step="0.01" min="0" required></div>
          <div class="form-field"><label for="offer_valid_until">Gueltig bis</label><input id="offer_valid_until" name="valid_until" type="date"></div>
          <div class="form-field"><label for="offer_notes">Notiz</label><input id="offer_notes" name="notes"></div>
        </div>
        <div class="profile-actions"><button type="submit">Angebot erstellen</button></div>
      </form>

      <?php if ($offers === []): ?>
        <p class="company-log-empty">Noch keine Angebote vorhanden.</p>
      <?php else: ?>
        <div class="status-list">
          <?php foreach ($offers as $offer): ?>
            <article class="status-item">
              <div>
                <p class="status-item-name"><?= $this->escape((string) ($offer['number'] ?? 'ANG-?')) ?> · <?= $this->escape((string) ($offer['customer_name'] ?? '')) ?></p>
                <p><?= $this->escape((string) ($offer['amount_eur'] ?? '0.00')) ?> EUR<?= (string) ($offer['valid_until'] ?? '') !== '' ? ' · gueltig bis ' . $this->escape((string) $offer['valid_until']) : '' ?></p>
              </div>
              <form method="post" action="/documents/delete" class="status-item-delete">
                <input type="hidden" name="type" value="offers">
                <input type="hidden" name="id" value="<?= $this->escape((string) ($offer['id'] ?? '')) ?>">
                <a href="/documents/pdf?type=offers&id=<?= $this->escape((string) ($offer['id'] ?? '')) ?>" class="secondary-link" target="_blank" rel="noopener noreferrer">PDF</a>
                <button type="submit" class="mini-danger">Loeschen</button>
              </form>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <section class="company-section-card is-hidden" data-documents-view="invoices">
      <header>
        <h3>Rechnung erstellen</h3>
        <p>Kunden waehlen, Positionen erfassen und Gesamtrabatt anwenden.</p>
      </header>
      <form method="post" action="/documents/invoices" class="profile-form">
        <div class="profile-grid">
          <div class="form-field">
            <label for="invoice_customer">Kunde</label>
            <select id="invoice_customer" name="customer_name" class="status-select" required>
              <option value="">Bitte auswaehlen</option>
              <?php foreach ($customerOptions as $customerOption): ?>
                <option value="<?= $this->escape($customerOption) ?>"><?= $this->escape($customerOption) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field"><label for="invoice_due_date">Faellig am</label><input id="invoice_due_date" name="due_date" type="date"></div>
          <div class="form-field"><label for="invoice_discount">Rabatt auf Gesamtleistung (%)</label><input id="invoice_discount" name="discount_percent" type="number" min="0" max="100" step="0.01" value="0" data-invoice-discount></div>
          <div class="form-field"><label for="invoice_vat">MwSt. (%)</label><input id="invoice_vat" name="vat_percent" type="number" min="0" max="100" step="0.01" value="19" data-invoice-vat></div>
          <div class="form-field"><label for="invoice_notes">Notiz</label><input id="invoice_notes" name="notes"></div>
        </div>

        <div class="form-field">
          <label>Positionen</label>
          <div class="kv-module" data-invoice-items-module>
            <div class="kv-rows" data-invoice-items-rows>
              <div class="kv-row" data-invoice-item-row>
                <input type="text" name="item_description[]" placeholder="Beschreibung, z. B. Webdesign" required>
                <input type="number" name="item_quantity[]" min="0.01" step="0.01" placeholder="Menge" required data-invoice-item-qty>
                <input type="number" name="item_unit_price[]" min="0" step="0.01" placeholder="Einzelpreis (EUR)" required data-invoice-item-price>
                <button type="button" class="kv-remove" data-invoice-item-remove>Entfernen</button>
              </div>
            </div>
            <button type="button" class="kv-add" data-invoice-item-add>+ Position hinzufuegen</button>
          </div>
        </div>

        <div class="company-profile-grid" data-invoice-summary>
          <article class="company-profile-item">
            <h4>Zwischensumme</h4>
            <p><span data-invoice-subtotal>0.00</span> EUR</p>
          </article>
          <article class="company-profile-item">
            <h4>Rabatt</h4>
            <p><span data-invoice-discount-amount>0.00</span> EUR</p>
          </article>
          <article class="company-profile-item">
            <h4>MwSt.</h4>
            <p><span data-invoice-vat-amount>0.00</span> EUR</p>
          </article>
          <article class="company-profile-item">
            <h4>Gesamtbetrag</h4>
            <p><strong><span data-invoice-gross-total>0.00</span> EUR</strong></p>
          </article>
        </div>
        <div class="profile-actions"><button type="submit">Rechnung erstellen</button></div>
      </form>

      <?php if ($invoices === []): ?>
        <p class="company-log-empty">Noch keine Rechnungen vorhanden.</p>
      <?php else: ?>
        <div class="status-list">
          <?php foreach ($invoices as $invoice): ?>
            <?php
            $lineItems = is_array($invoice['line_items'] ?? null) ? $invoice['line_items'] : [];
            $createdTs = strtotime((string) ($invoice['created_at'] ?? ''));
            $isEditable = $createdTs !== false && (time() - $createdTs) <= 43200;
            $hoursLeft = $createdTs !== false ? max(0, (int) ceil((43200 - (time() - $createdTs)) / 3600)) : 0;
            ?>
            <article class="status-item">
              <div>
                <p class="status-item-name"><?= $this->escape((string) ($invoice['number'] ?? 'RE-?')) ?> · <?= $this->escape((string) ($invoice['customer_name'] ?? '')) ?></p>
                <p>
                  <?= $this->escape((string) ($invoice['gross_total_eur'] ?? $invoice['amount_eur'] ?? '0.00')) ?> EUR
                  <?= (string) ($invoice['due_date'] ?? '') !== '' ? ' · faellig am ' . $this->escape((string) $invoice['due_date']) : '' ?>
                  <?= (string) ($invoice['discount_percent'] ?? '') !== '' ? ' · Rabatt ' . $this->escape((string) $invoice['discount_percent']) . '%' : '' ?>
                </p>
                <?php if ($lineItems !== []): ?>
                  <p><?= $this->escape((string) count($lineItems)) ?> Position<?= count($lineItems) === 1 ? '' : 'en' ?></p>
                <?php endif; ?>
              </div>
              <div class="status-item-delete">
                <a href="/documents/pdf?type=invoices&id=<?= $this->escape((string) ($invoice['id'] ?? '')) ?>" class="secondary-link" target="_blank" rel="noopener noreferrer">PDF</a>
                <?php if ($isEditable): ?>
                  <a href="/documents/invoices/edit?id=<?= $this->escape((string) ($invoice['id'] ?? '')) ?>" class="secondary-link" title="Noch <?= $hoursLeft ?> Std. bearbeitbar">Bearbeiten</a>
                <?php endif; ?>
                <form method="post" action="/documents/delete" style="display:inline">
                  <input type="hidden" name="type" value="invoices">
                  <input type="hidden" name="id" value="<?= $this->escape((string) ($invoice['id'] ?? '')) ?>">
                  <button type="submit" class="mini-danger">Loeschen</button>
                </form>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <section class="company-section-card is-hidden" data-documents-view="reminders">
      <header>
        <h3>Mahnung erstellen</h3>
        <p>Mahnung auf Basis einer Rechnungsnummer anlegen.</p>
      </header>
      <form method="post" action="/documents/reminders" class="profile-form">
        <div class="profile-grid">
          <div class="form-field">
            <label for="reminder_customer">Kunde</label>
            <select id="reminder_customer" name="customer_name" class="status-select" required>
              <option value="">Bitte auswaehlen</option>
              <?php foreach ($customerOptions as $customerOption): ?>
                <option value="<?= $this->escape($customerOption) ?>"><?= $this->escape($customerOption) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field"><label for="reminder_invoice_number">Rechnungsnummer</label><input id="reminder_invoice_number" name="invoice_number" required></div>
          <div class="form-field"><label for="reminder_amount">Betrag (EUR)</label><input id="reminder_amount" name="amount_eur" type="number" step="0.01" min="0" required></div>
          <div class="form-field">
            <label for="reminder_level">Mahnstufe</label>
            <select id="reminder_level" name="level" class="status-select">
              <option value="1">1. Mahnung</option>
              <option value="2">2. Mahnung</option>
              <option value="3">3. Mahnung</option>
            </select>
          </div>
          <div class="form-field"><label for="reminder_due_date">Neue Frist</label><input id="reminder_due_date" name="due_date" type="date"></div>
          <div class="form-field"><label for="reminder_notes">Notiz</label><input id="reminder_notes" name="notes"></div>
        </div>
        <div class="profile-actions"><button type="submit">Mahnung erstellen</button></div>
      </form>

      <?php if ($reminders === []): ?>
        <p class="company-log-empty">Noch keine Mahnungen vorhanden.</p>
      <?php else: ?>
        <div class="status-list">
          <?php foreach ($reminders as $reminder): ?>
            <article class="status-item">
              <div>
                <p class="status-item-name"><?= $this->escape((string) ($reminder['number'] ?? 'MAH-?')) ?> · <?= $this->escape((string) ($reminder['customer_name'] ?? '')) ?></p>
                <p>Rechnung <?= $this->escape((string) ($reminder['invoice_number'] ?? '-')) ?> · <?= $this->escape((string) ($reminder['amount_eur'] ?? '0.00')) ?> EUR · Stufe <?= $this->escape((string) ($reminder['level'] ?? '1')) ?></p>
              </div>
              <form method="post" action="/documents/delete" class="status-item-delete">
                <input type="hidden" name="type" value="reminders">
                <input type="hidden" name="id" value="<?= $this->escape((string) ($reminder['id'] ?? '')) ?>">
                <a href="/documents/pdf?type=reminders&id=<?= $this->escape((string) ($reminder['id'] ?? '')) ?>" class="secondary-link" target="_blank" rel="noopener noreferrer">PDF</a>
                <button type="submit" class="mini-danger">Loeschen</button>
              </form>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
</div>