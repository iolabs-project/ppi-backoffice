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
        preConfirm: async (value) => {
            try {
                const response = await axios.post(route('master.contacts.store'), {
                    name: value.trim(),
                    is_customer: type === 'customer',
                    is_supplier: type === 'supplier',
                    is_employee: false,
                });
                return response.data.data;
            } catch (error) {
                Swal.showValidationMessage(
                    Object.values(error.response?.data?.errors ?? {}).flat()[0]
                    ?? error.response?.data?.message
                    ?? 'Gagal menyimpan kontak.'
                );
            }
        },
    });

    if (!result.isConfirmed) {
        return null;
    }
    Toast.fire({ icon: 'success', title: `${label} berhasil ditambahkan` });
    return result.value;
}
