document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('table[data-datatable]').forEach((table) => {
        new DataTable(table, { order: [], pageLength: 10 });

        // Let wide tables scroll sideways on phones instead of stretching the page.
        const scroller = document.createElement('div');
        scroller.className = 'table-responsive';
        table.before(scroller);
        scroller.append(table);
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

// Forms with data-confirm ask first in the shared Bootstrap modal (#confirm-modal in the dashboard layout).
// The confirm button copies the form's own submit button, e.g. a red "Delete" or "Approve & refund".
let pendingConfirmForm = null;

// Show field errors Bootstrap-style (red border + message under the field) instead of the browser's popups.
// The server still validates everything; this only replaces how the browser's own checks are displayed.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form').forEach((form) => { form.noValidate = true; });
});

function showFieldErrors(form) {
    let firstInvalid = null;
    form.querySelectorAll('input, select, textarea').forEach((field) => {
        const holder = field.closest('.form-check') ?? field;
        let feedback = holder.parentElement.querySelector(':scope > .invalid-feedback[data-client]');

        if (field.checkValidity()) {
            field.classList.remove('is-invalid');
            feedback?.remove();
            return;
        }

        field.classList.add('is-invalid');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback d-block';
            feedback.dataset.client = '';
            holder.after(feedback);
        }
        feedback.textContent = field.validationMessage;
        firstInvalid ??= field;
    });
    firstInvalid?.focus();
}

// Clear a field's error as soon as it is corrected.
['input', 'change'].forEach((type) => document.addEventListener(type, (event) => {
    const field = event.target;
    if (field.classList?.contains('is-invalid') && field.checkValidity()) {
        field.classList.remove('is-invalid');
        (field.closest('.form-check') ?? field).parentElement
            .querySelector(':scope > .invalid-feedback[data-client]')?.remove();
    }
}));

document.addEventListener('submit', (event) => {
    if (event.target instanceof HTMLFormElement && !event.target.checkValidity()) {
        event.preventDefault();
        showFieldErrors(event.target);
        return;
    }

    const form = event.target.closest('form[data-confirm]');
    if (!form) {
        return;
    }

    const modalEl = document.getElementById('confirm-modal');
    if (!modalEl) {
        // Page without the dashboard layout: fall back to the browser's dialog.
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
        return;
    }

    event.preventDefault();
    pendingConfirmForm = form;

    const submitButton = form.querySelector('[type="submit"]');
    const acceptButton = modalEl.querySelector('[data-confirm-accept]');
    acceptButton.textContent = submitButton ? submitButton.textContent.trim() : 'Confirm';
    acceptButton.className = `btn ${submitButton?.classList.contains('btn-danger') ? 'btn-danger' : 'btn-primary'}`;
    modalEl.querySelector('[data-confirm-message]').textContent = form.dataset.confirm;

    bootstrap.Modal.getOrCreateInstance(modalEl).show();
});

document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-confirm-accept]') || !pendingConfirmForm) {
        return;
    }

    const form = pendingConfirmForm;
    pendingConfirmForm = null;
    event.target.closest('[data-confirm-accept]').disabled = true; // no double submits
    form.submit(); // submit() skips the submit event, so the dialog doesn't open again
});

document.addEventListener('hidden.bs.modal', (event) => {
    if (event.target.id === 'confirm-modal') {
        pendingConfirmForm = null;
        event.target.querySelector('[data-confirm-accept]').disabled = false;
    }
});