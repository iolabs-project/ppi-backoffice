// Field-level validation feedback shared by every form.
//
// Fields opt in through data attributes, which <x-misc.field> renders for you:
//   data-field                 marks the wrapper that receives the error state
//   data-field-label="Gudang"  name used in the error toast
//   data-field-required        must be filled before a confirmation dialog is shown
//   data-field-name="warehouse_id"  request key, so a 422 error for it lands on this field
//   data-field-compact         (table cells) outline only, the message goes in the tooltip

const INVALID = 'field--invalid';

function isVisible(el) {
    return el.getClientRects().length > 0;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function isEmpty(field) {
    const dropdown = field.querySelector('.dropdown-wrap');
    if (dropdown) {
        return dropdown.dataset.hasValue !== 'true';
    }

    const controls = [...field.querySelectorAll('input, select, textarea')]
        .filter(c => c.type !== 'hidden' && !c.closest('.dropdown-search'));
    const editable = controls.filter(c => !c.disabled && !c.readOnly);
    // Auto-filled or locked fields (e.g. document numbers) are not the user's to fill in
    if (editable.length === 0) {
        return false;
    }

    const choices = editable.filter(c => c.type === 'checkbox' || c.type === 'radio');
    if (choices.length) {
        return !choices.some(c => c.checked);
    }

    return editable.every(c => String(c.value ?? '').trim() === '');
}

function unmarkField(field) {
    field.classList.remove(INVALID);
    delete field.dataset.fieldReason;
    field.querySelector(':scope > .field__error')?.remove();
    if (field.hasAttribute('data-field-compact')) {
        field.removeAttribute('title');
    }
}

// A server error goes away as soon as the user edits that field again
function watch(field) {
    if (field._fieldValidationWatched) {
        return;
    }
    field._fieldValidationWatched = true;

    const onEdit = (event) => {
        if (field.dataset.fieldReason === 'server' && !event.target.closest('.dropdown-search')) {
            unmarkField(field);
        }
    };
    field.addEventListener('input', onEdit);
    field.addEventListener('change', onEdit);

    const dropdown = field.querySelector('.dropdown-wrap');
    if (dropdown) {
        new MutationObserver(() => {
            if (field.dataset.fieldReason === 'server') {
                unmarkField(field);
            }
        }).observe(dropdown, { attributes: true, attributeFilter: ['data-has-value'] });
    }
}

// A "required" error goes away once the field has a value, even when a handler filled it in
// (e.g. picking a payment term sets the due date). Checked after any interaction on the page.
let recheckQueued = false;
function recheckRequired() {
    if (recheckQueued) {
        return;
    }
    recheckQueued = true;
    setTimeout(() => {
        recheckQueued = false;
        document.querySelectorAll(`.${INVALID}[data-field-reason="required"]`).forEach(field => {
            if (!isEmpty(field)) {
                unmarkField(field);
            }
        });
    });
}
['click', 'input', 'change'].forEach(type => document.addEventListener(type, recheckRequired, true));

function markField(field, message, reason) {
    field.classList.add(INVALID);
    field.dataset.fieldReason = reason;
    if (field.hasAttribute('data-field-compact')) {
        field.title = message;
    } else {
        let el = field.querySelector(':scope > .field__error');
        if (!el) {
            el = document.createElement('div');
            el.className = 'field__error';
            field.appendChild(el);
        }
        el.textContent = message;
    }
    watch(field);
}

export function clearErrors(root = document) {
    root.querySelectorAll(`.${INVALID}`).forEach(unmarkField);
}

// Checks the visible required fields inside `root`. Call it before showing a confirmation dialog;
// returns false (and shows which fields are missing) when something still needs to be filled in.
export function validateRequired(root = document) {
    clearErrors(root);

    const missing = [];
    root.querySelectorAll('[data-field-required]').forEach(field => {
        if (!isVisible(field) || !isEmpty(field)) {
            return;
        }
        markField(field, 'Wajib diisi.', 'required');
        missing.push(field.dataset.fieldLabel);
    });

    if (missing.length === 0) {
        return true;
    }

    Toast.fire({
        icon: 'error',
        title: 'Lengkapi data yang wajib diisi.',
        html: '<ul style="text-align:left; margin:0; padding-left:20px;">' +
            missing.map(label => `<li>${escapeHtml(label)}</li>`).join('') + '</ul>',
    });
    root.querySelector(`.${INVALID}`)?.scrollIntoView({ block: 'center', behavior: 'smooth' });
    return false;
}

// Marks the fields named in a Laravel 422 `errors` bag. Keys without a matching field
// are still listed in the toast each form already shows.
export function showServerErrors(errors, root = document) {
    clearErrors(root);

    Object.entries(errors ?? {}).forEach(([key, messages]) => {
        root.querySelectorAll(`[data-field-name="${CSS.escape(key)}"]`).forEach(field => {
            if (isVisible(field)) {
                markField(field, [].concat(messages)[0], 'server');
            }
        });
    });

    document.querySelector(`.${INVALID}`)?.scrollIntoView({ block: 'center', behavior: 'smooth' });
}
