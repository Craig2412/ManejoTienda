import './bootstrap';
import TomSelect from 'tom-select';

const initSearchableSelects = () => {
	const selects = document.querySelectorAll('select');

	selects.forEach((select) => {
		if (select.dataset.searchable === 'false' || select.disabled) {
			return;
		}

		const isMultiple = select.multiple;
		const emptyOption = select.querySelector('option[value=""]');
		const placeholder = select.dataset.placeholder || emptyOption?.textContent?.trim() || 'Selecciona una opcion';

		if (select.tomselect) {
			select.tomselect.destroy();
		}

		new TomSelect(select, {
			allowEmptyOption: true,
			create: false,
			maxItems: isMultiple ? null : 1,
			closeAfterSelect: !isMultiple,
			placeholder,
			plugins: ['dropdown_input'],
		});
	});
};

document.addEventListener('DOMContentLoaded', initSearchableSelects);

const initPaymentsTableFilter = () => {
	const saleTypeSelect = document.querySelector('[data-sale-type]');
	const tableFilter = document.querySelector('[data-table-filter]');

	if (!saleTypeSelect || !tableFilter) {
		return;
	}

	const tableSelect = tableFilter.querySelector('select');

	const toggleTableFilter = () => {
		const isMesa = saleTypeSelect.value === 'mesa';
		tableFilter.classList.toggle('hidden', !isMesa);

		if (!isMesa && tableSelect) {
			tableSelect.value = '';
			if (tableSelect.tomselect) {
				tableSelect.tomselect.clear();
			}
		}
	};

	toggleTableFilter();
	saleTypeSelect.addEventListener('change', toggleTableFilter);
};

document.addEventListener('DOMContentLoaded', initPaymentsTableFilter);
