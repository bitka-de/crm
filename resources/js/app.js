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