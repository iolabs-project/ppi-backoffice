const TYPE_LABELS = { customer: 'Customer', supplier: 'Vendor', employee: 'Karyawan' };

async function storeContact(name, types) {
    try {
        const response = await axios.post(route('master.contacts.store'), {
            name: name.trim(),
            is_customer: types.includes('customer'),
            is_supplier: types.includes('supplier'),
            is_employee: types.includes('employee'),
        });
        return response.data.data;
    } catch (error) {
        Swal.showValidationMessage(
            Object.values(error.response?.data?.errors ?? {}).flat()[0]
            ?? error.response?.data?.message
            ?? 'Gagal menyimpan kontak.'
        );
    }
}

// Create a customer/supplier straight from a form's contact dropdown (the code is generated server-side).
// Resolves to the new contact { id, code, name, transportation_cost }, or null when cancelled.
export async function quickAdd(type, name = '') {
    const label = type === 'supplier' ? 'Supplier' : 'Customer';
    const result = await Swal.fire({
        title: `Tambah ${label} Baru`,
        input: 'text',
        inputValue: name,
        inputPlaceholder: 'Nama perusahaan / individu',
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        inputValidator: (value) => !value?.trim() && 'Nama wajib diisi.',
        preConfirm: (value) => storeContact(value, [type]),
    });

    if (!result.isConfirmed) {
        return null;
    }
    Toast.fire({ icon: 'success', title: `${label} berhasil ditambahkan` });
    return result.value;
}

// Same as quickAdd, but for dropdowns listing every contact: the user ticks one or more types,
// with `defaultTypes` pre-ticked.
export async function quickAddWithType(name = '', defaultTypes = []) {
    const checkboxes = Object.entries(TYPE_LABELS).map(([type, label]) => `
        <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
            <input type="checkbox" name="contact_type" value="${type}" style="accent-color:var(--accent);"
                ${defaultTypes.includes(type) ? 'checked' : ''} />
            ${label}
        </label>`).join('');

    const result = await Swal.fire({
        title: 'Tambah Kontak Baru',
        html: `
            <input id="quick-contact-name" class="swal2-input" placeholder="Nama perusahaan / individu"
                style="width:100%; margin:0 0 14px; box-sizing:border-box;" />
            <div style="display:flex; justify-content:center; gap:16px;">${checkboxes}</div>`,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        didOpen: (popup) => {
            const input = popup.querySelector('#quick-contact-name');
            input.value = name;
            input.focus();
        },
        preConfirm: () => {
            const popup = Swal.getPopup();
            const value = popup.querySelector('#quick-contact-name').value;
            const types = [...popup.querySelectorAll('input[name="contact_type"]:checked')].map(el => el.value);
            if (!value.trim()) {
                Swal.showValidationMessage('Nama wajib diisi.');
                return false;
            }
            if (!types.length) {
                Swal.showValidationMessage('Pilih minimal satu tipe kontak.');
                return false;
            }
            return storeContact(value, types);
        },
    });

    if (!result.isConfirmed) {
        return null;
    }
    Toast.fire({ icon: 'success', title: 'Kontak berhasil ditambahkan' });
    return result.value;
}
