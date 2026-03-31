<?php

declare(strict_types=1);

/** @var array<string, mixed> $invoice */
/** @var list<string> $customerOptions */
/** @var string|null $error */

$lineItems = is_array($invoice['line_items'] ?? null) ? $invoice['line_items'] : [];
$createdTs = strtotime((string) ($invoice['created_at'] ?? ''));
$hoursLeft = $createdTs !== false ? max(0, (int) ceil((43200 - (time() - $createdTs)) / 3600)) : 0;
?>
<div class="component-stack">
    <?= $this->component('hero', [
        'kicker' => 'Rechnung bearbeiten',
        'title' => (string) ($invoice['number'] ?? ''),
        'description' => 'Positionen, Rabatt und Faelligkeit anpassen. Noch ' . $hoursLeft . ' Std. im Bearbeitungsfenster.',
    ]) ?>

    <?php if ($error !== null): ?>
        <div class="auth-error flash-message" data-flash-message role="alert" aria-live="assertive"><?= $this->escape($error) ?></div>
    <?php endif; ?>

    <section class="company-section-card">
        <header>
            <h3>Rechnung <?= $this->escape((string) ($invoice['number'] ?? '')) ?></h3>
            <p>Erstellt am <?= $this->escape((string) ($invoice['created_at'] ?? '')) ?> — Änderungen werden direkt gespeichert.</p>
        </header>

        <form method="post" action="/documents/invoices/update" class="profile-form">
            <input type="hidden" name="id" value="<?= $this->escape((string) ($invoice['id'] ?? '')) ?>">

            <div class="profile-grid">
                <div class="form-field">
                    <label for="edit_customer">Kunde</label>
                    <select id="edit_customer" name="customer_name" class="status-select" required>
                        <option value="">Bitte auswaehlen</option>
                        <?php foreach ($customerOptions as $customerOption): ?>
                            <option value="<?= $this->escape($customerOption) ?>"
                                <?= $customerOption === (string) ($invoice['customer_name'] ?? '') ? 'selected' : '' ?>>
                                <?= $this->escape($customerOption) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label for="edit_due_date">Faellig am</label>
                    <input id="edit_due_date" name="due_date" type="date" value="<?= $this->escape((string) ($invoice['due_date'] ?? '')) ?>">
                </div>
                <div class="form-field">
                    <label for="edit_discount">Rabatt auf Gesamtleistung (%)</label>
                    <input id="edit_discount" name="discount_percent" type="number" min="0" max="100" step="0.01"
                           value="<?= $this->escape((string) ($invoice['discount_percent'] ?? '0')) ?>"
                           data-invoice-discount>
                </div>
                <div class="form-field">
                    <label for="edit_vat">MwSt. (%)</label>
                    <input id="edit_vat" name="vat_percent" type="number" min="0" max="100" step="0.01"
                           value="<?= $this->escape((string) ($invoice['vat_percent'] ?? '19')) ?>"
                           data-invoice-vat>
                </div>
                <div class="form-field">
                    <label for="edit_notes">Notiz</label>
                    <input id="edit_notes" name="notes" value="<?= $this->escape((string) ($invoice['notes'] ?? '')) ?>">
                </div>
            </div>

            <div class="form-field">
                <label>Positionen</label>
                <div class="kv-module" data-invoice-items-module>
                    <div class="kv-rows" data-invoice-items-rows>
                        <?php if ($lineItems === []): ?>
                            <div class="kv-row" data-invoice-item-row>
                                <input type="text" name="item_description[]" placeholder="Beschreibung" required>
                                <input type="number" name="item_quantity[]" min="0.01" step="0.01" placeholder="Menge" required data-invoice-item-qty>
                                <input type="number" name="item_unit_price[]" min="0" step="0.01" placeholder="Einzelpreis (EUR)" required data-invoice-item-price>
                                <button type="button" class="kv-remove" data-invoice-item-remove>Entfernen</button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($lineItems as $item): ?>
                                <div class="kv-row" data-invoice-item-row>
                                    <input type="text" name="item_description[]"
                                           placeholder="Beschreibung"
                                           value="<?= $this->escape((string) ($item['description'] ?? '')) ?>"
                                           required>
                                    <input type="number" name="item_quantity[]" min="0.01" step="0.01"
                                           placeholder="Menge"
                                           value="<?= $this->escape((string) ($item['quantity'] ?? '')) ?>"
                                           required data-invoice-item-qty>
                                    <input type="number" name="item_unit_price[]" min="0" step="0.01"
                                           placeholder="Einzelpreis (EUR)"
                                           value="<?= $this->escape((string) ($item['unit_price_eur'] ?? '')) ?>"
                                           required data-invoice-item-price>
                                    <button type="button" class="kv-remove" data-invoice-item-remove>Entfernen</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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

            <div class="profile-actions">
                <a href="/documents" class="secondary-link">Abbrechen</a>
                <button type="submit">Änderungen speichern</button>
            </div>
        </form>
    </section>
</div>
