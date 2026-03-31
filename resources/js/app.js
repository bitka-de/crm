document.documentElement.classList.add('js-enabled');

document.querySelectorAll('[data-flash-message]').forEach((message, index) => {
	message.style.top = `${20 + index * 76}px`;

	window.setTimeout(() => {
		message.classList.add('is-fading');
		window.setTimeout(() => {
			message.remove();
		}, 300);
	}, 3000);
});

const companyView = document.querySelector('[data-company-view]');
const companyEditForm = document.querySelector('[data-company-edit]');
const companyLogView = document.querySelector('[data-company-log]');
const switchButtons = document.querySelectorAll('[data-company-switch]');
const companyShell = document.querySelector('[data-company-shell]');

if (companyView && companyEditForm && companyLogView) {
	const syncSwitchState = (mode) => {
		switchButtons.forEach((button) => {
			button.classList.toggle('is-active', button.getAttribute('data-company-switch') === mode);
		});
	};

	const setCompanyMode = (mode) => {
		const safeMode = mode === 'edit' || mode === 'log' ? mode : 'view';

		companyView.classList.toggle('is-hidden', safeMode !== 'view');
		companyEditForm.classList.toggle('is-hidden', safeMode !== 'edit');
		companyLogView.classList.toggle('is-hidden', safeMode !== 'log');

		if (companyShell) {
			companyShell.classList.toggle('is-edit', safeMode === 'edit');
			companyShell.classList.toggle('is-view', safeMode === 'view');
			companyShell.classList.toggle('is-log', safeMode === 'log');
		}

		syncSwitchState(safeMode);
	};

	switchButtons.forEach((button) => {
		button.addEventListener('click', () => {
			setCompanyMode(button.getAttribute('data-company-switch') || 'view');
		});
	});

	setCompanyMode(!companyEditForm.classList.contains('is-hidden') ? 'edit' : 'view');
}

const legalFormRadios = document.querySelectorAll('[data-legal-form-radio]');

if (legalFormRadios.length > 0) {
	const roleMap = {
		company_name: document.querySelector('[data-role="company_name"]'),
		owner_name: document.querySelector('[data-role="owner_name"]'),
		managing_director: document.querySelector('[data-role="managing_director"]'),
		registration_number: document.querySelector('[data-role="registration_number"]'),
		registration_court: document.querySelector('[data-role="registration_court"]'),
		share_capital_eur: document.querySelector('[data-role="share_capital_eur"]'),
	};

	const ownerLabel = document.querySelector('[data-label-owner]');
	const directorLabel = document.querySelector('[data-label-director]');
	const companyLabel = document.querySelector('[data-label-company]');
	const helpText = document.querySelector('[data-legal-form-help]');

	const setVisible = (role, visible) => {
		const field = roleMap[role];
		if (!field) {
			return;
		}

		field.classList.toggle('is-hidden', !visible);
	};

	const getSelectedLegalForm = () => {
		const checked = Array.from(legalFormRadios).find((radio) => radio.checked);
		return checked ? checked.value : 'UG (haftungsbeschraenkt)';
	};

	const applyLegalForm = () => {
		const form = getSelectedLegalForm();

		// Basis: alles sichtbar, dann je nach Rechtsform anpassen.
		setVisible('registration_number', true);
		setVisible('registration_court', true);
		setVisible('share_capital_eur', true);
		setVisible('managing_director', true);

		if (form === 'Freelancer') {
			if (companyLabel) companyLabel.textContent = 'Name (freiberuflich)';
			if (ownerLabel) ownerLabel.textContent = 'Name';
			if (directorLabel) directorLabel.textContent = 'Ansprechpartner';
			if (helpText) helpText.textContent = 'Freelancer benoetigen in der Regel keinen Handelsregistereintrag oder Stammkapital.';
			setVisible('registration_number', false);
			setVisible('registration_court', false);
			setVisible('share_capital_eur', false);
			setVisible('managing_director', false);
			return;
		}

		if (form === 'Einzelunternehmen') {
			if (companyLabel) companyLabel.textContent = 'Unternehmensname / Firmenbezeichnung';
			if (ownerLabel) ownerLabel.textContent = 'Inhaber';
			if (directorLabel) directorLabel.textContent = 'Ansprechpartner';
			if (helpText) helpText.textContent = 'Einzelunternehmen haben meist kein festes Stammkapital; Handelsregister ist optional.';
			setVisible('share_capital_eur', false);
			setVisible('managing_director', false);
			return;
		}

		if (form === 'GbR') {
			if (companyLabel) companyLabel.textContent = 'Name der GbR';
			if (ownerLabel) ownerLabel.textContent = 'Gesellschafter';
			if (directorLabel) directorLabel.textContent = 'Ansprechpartner';
			if (helpText) helpText.textContent = 'GbR: Gesellschafter wichtig, Stammkapital nicht erforderlich, Handelsregister nur falls vorhanden.';
			setVisible('share_capital_eur', false);
			setVisible('managing_director', false);
			return;
		}

		if (form === 'UG (haftungsbeschraenkt)') {
			if (companyLabel) companyLabel.textContent = 'Unternehmensname';
			if (ownerLabel) ownerLabel.textContent = 'Gesellschafter';
			if (directorLabel) directorLabel.textContent = 'Geschaeftsfuehrer';
			if (helpText) helpText.textContent = 'UG: Handelsregisterdaten und Stammkapital sind relevant.';
			return;
		}

		if (companyLabel) companyLabel.textContent = 'Unternehmensname';
		if (ownerLabel) ownerLabel.textContent = 'Gesellschafter';
		if (directorLabel) directorLabel.textContent = 'Geschaeftsfuehrer';
		if (helpText) helpText.textContent = 'GmbH: Handelsregisterdaten und Stammkapital sind relevant.';
	};

	applyLegalForm();
	legalFormRadios.forEach((radio) => {
		radio.addEventListener('change', applyLegalForm);
	});
}

const kvModule = document.querySelector('[data-kv-module]');

if (kvModule) {
	const rowsContainer = kvModule.querySelector('[data-kv-rows]');
	const addButton = kvModule.querySelector('[data-kv-add]');

	const createRow = () => {
		const row = document.createElement('div');
		row.className = 'kv-row';
		row.setAttribute('data-kv-row', '');
		row.innerHTML = [
			'<input type="text" name="extra_field_key[]" placeholder="key (z. B. iban)">',
			'<input type="text" name="extra_field_value[]" placeholder="value">',
			'<select name="extra_field_type[]">',
			'<option value="text">text</option>',
			'<option value="number">number</option>',
			'<option value="boolean">boolean</option>',
			'<option value="date">date</option>',
			'</select>',
			'<button type="button" class="kv-remove" data-kv-remove>Entfernen</button>',
		].join('');
		return row;
	};

	const bindRemoveButton = (button) => {
		button.addEventListener('click', () => {
			const row = button.closest('[data-kv-row]');

			if (!row) {
				return;
			}

			const ok = window.confirm('Willst du das wirklich entfernen?');
			if (!ok) {
				return;
			}

			row.remove();

			if (rowsContainer && rowsContainer.children.length === 0) {
				rowsContainer.appendChild(createRow());
				const newButton = rowsContainer.querySelector('[data-kv-remove]');
				if (newButton) {
					bindRemoveButton(newButton);
				}
			}
		});
	};

	kvModule.querySelectorAll('[data-kv-remove]').forEach((button) => {
		bindRemoveButton(button);
	});

	if (addButton && rowsContainer) {
		addButton.addEventListener('click', () => {
			const row = createRow();
			rowsContainer.appendChild(row);
			const removeButton = row.querySelector('[data-kv-remove]');
			if (removeButton) {
				bindRemoveButton(removeButton);
			}
		});
	}
}

const contactsShell = document.querySelector('[data-contacts-shell]');

if (contactsShell) {
	const contactPanels = contactsShell.querySelectorAll('[data-contacts-tab-panel]');
	const tabButtons = contactsShell.querySelectorAll('[data-contacts-switch]');
	const initialTabRaw = contactsShell.getAttribute('data-initial-tab');
	const initialTab = initialTabRaw === 'statuses' || initialTabRaw === 'companies' ? initialTabRaw : 'contacts';

	const setContactsTab = (tab) => {
		const safeTab = tab === 'statuses' || tab === 'companies' ? tab : 'contacts';

		contactPanels.forEach((panel) => {
			panel.classList.toggle('is-hidden', panel.getAttribute('data-contacts-tab-panel') !== safeTab);
		});

		tabButtons.forEach((button) => {
			button.classList.toggle('is-active', button.getAttribute('data-contacts-switch') === safeTab);
		});
	};

	tabButtons.forEach((button) => {
		button.addEventListener('click', () => {
			setContactsTab(button.getAttribute('data-contacts-switch') || 'contacts');
		});
	});

	setContactsTab(initialTab);
}

const makeDialogController = (dialogSelector, openSelector, closeSelector, autoOpenAttr) => {
	const dialog = document.querySelector(dialogSelector);
	if (!dialog) return;

	const openDialog = () => {
		if (typeof dialog.showModal === 'function') {
			dialog.showModal();
		} else {
			dialog.setAttribute('open', 'open');
		}
	};

	const closeDialog = () => {
		if (typeof dialog.close === 'function') {
			dialog.close();
		} else {
			dialog.removeAttribute('open');
		}
	};

	document.querySelectorAll(openSelector).forEach((btn) => btn.addEventListener('click', openDialog));
	document.querySelectorAll(closeSelector).forEach((btn) => btn.addEventListener('click', closeDialog));

	dialog.addEventListener('click', (event) => {
		const bounds = dialog.getBoundingClientRect();
		const outside = event.clientX < bounds.left
			|| event.clientX > bounds.right
			|| event.clientY < bounds.top
			|| event.clientY > bounds.bottom;
		if (outside) closeDialog();
	});

	if (autoOpenAttr && dialog.getAttribute(autoOpenAttr) === '1') {
		openDialog();
	}
};

makeDialogController('[data-status-dialog]', '[data-open-status-dialog]', '[data-close-status-dialog]', null);
makeDialogController('[data-company-dialog]', '[data-open-company-dialog]', '[data-close-company-dialog]', 'data-company-dialog-auto-open');

// Legal-form-aware field visibility for the company dialog
const cdfDialog = document.querySelector('[data-company-dialog]');
const cdfLfSelect = cdfDialog ? cdfDialog.querySelector('[data-company-dialog-legal-form]') : null;

if (cdfDialog && cdfLfSelect) {
	const setCdfVisible = (role, visible) => {
		cdfDialog.querySelectorAll(`[data-cdf-role="${role}"]`).forEach((el) => {
			el.classList.toggle('is-hidden', !visible);
		});
	};
	const setCdfLabel = (attr, text) => {
		const el = cdfDialog.querySelector(`[data-cdf-label="${attr}"]`);
		if (el) el.textContent = text;
	};
	const applyCdfLegalForm = () => {
		const lf = cdfLfSelect.value;
		const helpEl = cdfDialog.querySelector('[data-cdf-lf-help]');
		// reset to show-all defaults
		setCdfVisible('owner_name', true);
		setCdfVisible('managing_director', true);
		setCdfVisible('registration_number', true);
		setCdfVisible('registration_court', true);
		setCdfVisible('share_capital_eur', true);
		setCdfLabel('owner_name', 'Inhaber / Gesellschafter');
		setCdfLabel('managing_director', 'Geschäftsführer');
		if (helpEl) helpEl.textContent = '';
		if (lf === 'Freelancer') {
			setCdfVisible('managing_director', false);
			setCdfVisible('registration_number', false);
			setCdfVisible('registration_court', false);
			setCdfVisible('share_capital_eur', false);
			setCdfLabel('owner_name', 'Name (freiberuflich)');
			if (helpEl) helpEl.textContent = 'Kein Handelsregistereintrag oder Stammkapital erforderlich.';
			return;
		}
		if (lf === 'Einzelunternehmen') {
			setCdfVisible('managing_director', false);
			setCdfVisible('share_capital_eur', false);
			setCdfLabel('owner_name', 'Inhaber');
			if (helpEl) helpEl.textContent = 'Stammkapital nicht erforderlich.';
			return;
		}
		if (lf === 'GbR') {
			setCdfVisible('managing_director', false);
			setCdfVisible('share_capital_eur', false);
			setCdfLabel('owner_name', 'Gesellschafter');
			if (helpEl) helpEl.textContent = 'Stammkapital nicht erforderlich.';
			return;
		}
		if (lf === 'UG (haftungsbeschraenkt)') {
			setCdfVisible('owner_name', false);
			if (helpEl) helpEl.textContent = 'Handelsregisterdaten und Stammkapital sind relevant.';
			return;
		}
		if (lf === 'GmbH') {
			setCdfVisible('owner_name', false);
			if (helpEl) helpEl.textContent = 'Handelsregisterdaten und Stammkapital sind relevant.';
			return;
		}
	};
	cdfLfSelect.addEventListener('change', applyCdfLegalForm);
	applyCdfLegalForm();

	// KV module for company extra fields
	const cdfKvRows = cdfDialog.querySelector('[data-cdf-kv-rows]');
	const cdfKvAdd = cdfDialog.querySelector('[data-cdf-kv-add]');
	const makeCompanyKvRow = () => {
		const row = document.createElement('div');
		row.className = 'kv-row';
		row.setAttribute('data-cdf-kv-row', '');
		row.innerHTML = `
			<input type="text" name="extra_field_key[]" placeholder="key (z. B. iban)">
			<input type="text" name="extra_field_value[]" placeholder="value">
			<select name="extra_field_type[]">
				<option value="text">text</option>
				<option value="number">number</option>
				<option value="boolean">boolean</option>
				<option value="date">date</option>
			</select>
			<button type="button" class="kv-remove" data-cdf-kv-remove>Entfernen</button>
		`;
		return row;
	};
	if (cdfKvRows && cdfKvAdd) {
		cdfKvRows.addEventListener('click', (e) => {
			if (e.target.closest('[data-cdf-kv-remove]')) {
				const row = e.target.closest('[data-cdf-kv-row]');
				if (row && cdfKvRows.querySelectorAll('[data-cdf-kv-row]').length > 1) {
					row.remove();
				} else if (row) {
					row.querySelectorAll('input').forEach((i) => (i.value = ''));
				}
			}
		});
		cdfKvAdd.addEventListener('click', () => {
			cdfKvRows.appendChild(makeCompanyKvRow());
		});
	}
}

const contactSearchInput = document.querySelector('[data-contact-search]');
const contactSearchEntries = document.querySelectorAll('[data-contact-entry]');
const contactSearchMeta = document.querySelector('[data-contact-search-meta]');
const contactSearchEmpty = document.querySelector('[data-contact-search-empty]');

if (contactSearchInput && contactSearchEntries.length > 0) {
	const applyContactSearch = () => {
		const query = contactSearchInput.value.trim().toLowerCase();
		let visibleCount = 0;

		contactSearchEntries.forEach((entry) => {
			const haystack = (entry.getAttribute('data-search-text') || '').toLowerCase();
			const match = query === '' || haystack.includes(query);

			entry.classList.toggle('is-hidden', !match);
			if (match) {
				visibleCount += 1;
			}
		});

		if (contactSearchMeta) {
			contactSearchMeta.textContent = `${visibleCount} von ${contactSearchEntries.length} sichtbar`;
		}

		if (contactSearchEmpty) {
			contactSearchEmpty.classList.toggle('is-hidden', visibleCount > 0);
		}
	};

	contactSearchInput.addEventListener('input', applyContactSearch);
	applyContactSearch();
}

makeDialogController('[data-contact-dialog]', '[data-open-contact-dialog]', '[data-close-contact-dialog]', 'data-contact-dialog-auto-open');

const invoiceItemsModule = document.querySelector('[data-invoice-items-module]');

if (invoiceItemsModule) {
	const rowsContainer = invoiceItemsModule.querySelector('[data-invoice-items-rows]');
	const addButton = invoiceItemsModule.querySelector('[data-invoice-item-add]');
	const discountInput = document.querySelector('[data-invoice-discount]');
	const vatInput = document.querySelector('[data-invoice-vat]');
	const subTotalEl = document.querySelector('[data-invoice-subtotal]');
	const discountAmountEl = document.querySelector('[data-invoice-discount-amount]');
	const vatAmountEl = document.querySelector('[data-invoice-vat-amount]');
	const grossTotalEl = document.querySelector('[data-invoice-gross-total]');

	const parseNum = (value) => {
		const normalized = String(value || '').replace(',', '.');
		const numeric = Number(normalized);
		return Number.isFinite(numeric) ? numeric : 0;
	};

	const fmt = (value) => (Math.round((value + Number.EPSILON) * 100) / 100).toFixed(2);

	const createRow = () => {
		const row = document.createElement('div');
		row.className = 'kv-row';
		row.setAttribute('data-invoice-item-row', '');
		row.innerHTML = [
			'<input type="text" name="item_description[]" placeholder="Beschreibung, z. B. Webdesign" required>',
			'<input type="number" name="item_quantity[]" min="0.01" step="0.01" placeholder="Menge" required data-invoice-item-qty>',
			'<input type="number" name="item_unit_price[]" min="0" step="0.01" placeholder="Einzelpreis (EUR)" required data-invoice-item-price>',
			'<button type="button" class="kv-remove" data-invoice-item-remove>Entfernen</button>',
		].join('');
		return row;
	};

	const recalc = () => {
		if (!rowsContainer) return;

		let subtotal = 0;
		rowsContainer.querySelectorAll('[data-invoice-item-row]').forEach((row) => {
			const qtyInput = row.querySelector('[data-invoice-item-qty]');
			const priceInput = row.querySelector('[data-invoice-item-price]');
			const qty = parseNum(qtyInput ? qtyInput.value : '0');
			const price = parseNum(priceInput ? priceInput.value : '0');
			if (qty > 0 && price >= 0) {
				subtotal += qty * price;
			}
		});

		const discountPercent = Math.min(100, Math.max(0, parseNum(discountInput ? discountInput.value : '0')));
		const vatPercent = Math.min(100, Math.max(0, parseNum(vatInput ? vatInput.value : '0')));
		const discountAmount = subtotal * (discountPercent / 100);
		const net = Math.max(0, subtotal - discountAmount);
		const vatAmount = net * (vatPercent / 100);
		const gross = net + vatAmount;

		if (subTotalEl) subTotalEl.textContent = fmt(subtotal);
		if (discountAmountEl) discountAmountEl.textContent = fmt(discountAmount);
		if (vatAmountEl) vatAmountEl.textContent = fmt(vatAmount);
		if (grossTotalEl) grossTotalEl.textContent = fmt(gross);
	};

	if (rowsContainer) {
		rowsContainer.addEventListener('click', (event) => {
			if (!event.target.closest('[data-invoice-item-remove]')) {
				return;
			}

			const row = event.target.closest('[data-invoice-item-row]');
			if (!row) {
				return;
			}

			const allRows = rowsContainer.querySelectorAll('[data-invoice-item-row]');
			if (allRows.length > 1) {
				row.remove();
			} else {
				row.querySelectorAll('input').forEach((input) => {
					input.value = '';
				});
			}

			recalc();
		});

		rowsContainer.addEventListener('input', (event) => {
			if (event.target.closest('[data-invoice-item-row]')) {
				recalc();
			}
		});
	}

	if (addButton && rowsContainer) {
		addButton.addEventListener('click', () => {
			rowsContainer.appendChild(createRow());
			recalc();
		});
	}

	if (discountInput) {
		discountInput.addEventListener('input', recalc);
	}

	if (vatInput) {
		vatInput.addEventListener('input', recalc);
	}

	recalc();
}

// Document tabs switching
const initDocumentTabs = () => {
	const documentsShell = document.querySelector('[data-documents-shell]');
	const documentsTabButtons = document.querySelectorAll('[data-documents-switch]');

	if (!documentsShell || documentsTabButtons.length === 0) {
		return;
	}

	const syncTabState = (tab) => {
		documentsTabButtons.forEach((button) => {
			button.classList.toggle('is-active', button.getAttribute('data-documents-switch') === tab);
		});
	};

	const setActiveTab = (tab) => {
		const validTabs = ['offers', 'invoices', 'reminders'];
		const safeTab = validTabs.includes(tab) ? tab : 'offers';

		document.querySelectorAll('[data-documents-view]').forEach((view) => {
			const viewTab = view.getAttribute('data-documents-view');
			const shouldHide = viewTab !== safeTab;
			view.classList.toggle('is-hidden', shouldHide);
		});

		syncTabState(safeTab);
	};

	documentsTabButtons.forEach((button) => {
		button.addEventListener('click', (e) => {
			e.preventDefault();
			const tabName = button.getAttribute('data-documents-switch') || 'offers';
			setActiveTab(tabName);
		});
	});

	setActiveTab('offers');
};

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initDocumentTabs);
} else {
	initDocumentTabs();
}