document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('table[data-datatable]').forEach((table) => {
        new DataTable(table, { order: [], pageLength: 10 });
    });
});

// Delegated handlers keep working for rows that DataTables moves in and out of the page.
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-crud-open]');
    if (!button) {
        return;
    }

    const modalEl = document.querySelector(button.dataset.crudModal);
    const form = modalEl.querySelector('form');
    const values = button.dataset.crudValues ? JSON.parse(button.dataset.crudValues) : {};

    form.reset();
    form.action = button.dataset.crudAction;
    modalEl.querySelector('.modal-title').textContent = button.dataset.crudTitle;

    Object.entries(values).forEach(([key, value]) => {
        const field = form.querySelector(`[name$="[${key}]"]`);
        if (!field) {
            return;
        }
        if (field.type === 'checkbox') {
            field.checked = Boolean(value);
        } else {
            field.value = value ?? '';
        }
    });

    bootstrap.Modal.getOrCreateInstance(modalEl).show();
});

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-confirm]');
    if (form && !window.confirm(form.dataset.confirm)) {
        event.preventDefault();
    }
});