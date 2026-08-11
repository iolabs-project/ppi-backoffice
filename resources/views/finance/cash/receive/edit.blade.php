@extends('layouts.app')
@section('content')
    <script>
        const transaction = @json($transaction);

        function receiveModule() {
            return {
                formData: {
                    to_account_id: null,
                    subtotal: null,
                    transaction_date: transaction.transaction_date || "{{ now()->format('Y-m-d') }}",
                    number: transaction.number || null,
                    type: 'receive',
                    description: transaction.description || null,
                    tax_percentage: transaction.tax_percentage || null,
                    tax_amount: null,
                    total_amount: null,
                    items: (transaction.items || []).map(item => ({
                        account_id: item.account_id,
                        description: item.description,
                        amount: item.amount
                    })),
                },
                account: @json($account),
                accounts: @json($accounts),

                contacts: @json($contacts),
                contactSelected: null,

                // Shorthand: parse masked string to number
                n(v) {
                    return NumberUtils.parseMaskIntoNumeric(v);
                },

                m(v) {
                    return NumberUtils.formatNumericIntoMask(v);
                },

                initials(name) {
                    return name ? name.split(' ').slice(0, 2).map(w => w[0]).join('') : '?';
                },

                addRow() {
                    this.formData.items.push({
                        account_id: null,
                        description: null,
                        amount: null
                    });
                },

                removeRow(i) {
                    if (this.formData.items.length > 1) this.formData.items.splice(i, 1);
                    this.calculate();
                },

                calculate() {
                    this.formData.subtotal = this.formData.items.reduce((sum, item) => sum + (this.n(item.amount) || 0), 0);
                    this.formData.tax_amount = (this.n(this.formData.tax_percentage) / 100) * this.formData.subtotal;
                    this.formData.total_amount = this.formData.subtotal + this.formData.tax_amount;
                },

                async submit(status) {
                    let isValid = false;
                    if (status === 'draft') {
                        this.formData.status = 'draft';
                        isValid = true;
                    } else if (status === 'posted') {
                        this.formData.status = 'posted';

                        const result = await Swal.fire({
                            title: 'Konfirmasi Terima Dana',
                            text: 'Apakah anda yakin ingin melakukan penerimaan dana dengan data yang telah diisi?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, terima',
                            cancelButtonText: 'Batal',
                            reverseButtons: true,
                        });
                        isValid = result.isConfirmed;
                    }

                    if (isValid) {
                        const titles = {
                            draft: 'Memproses penyimpanan draft penerimaan dana...',
                            posted: 'Memproses penerimaan dana...',
                        };
                        Swal.fire({
                            title: titles[status] ?? 'Memproses...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading(),
                        });
                        let body = {
                            ...this.formData,
                            subtotal: this.n(this.formData.subtotal),
                            to_account_id: this.account.id,
                            description: 'Terima Dana' + (this.contactSelected ? ' dari ' + this.contactSelected
                                .name : ''),
                        };
                        body.items = body.items.map(item => ({
                            ...item,
                            amount: this.n(item.amount)
                        }));
                        try {
                            const response = await axios.put(route('finances.cash.receive.update', {
                                    id: this.account.id,
                                    receive: transaction.id
                                }),
                                body);
                            Swal.close();
                            Toast.fire({
                                icon: 'success',
                                title: response.data.message
                            });
                            window.location.href = response.data.redirect;
                        } catch (error) {
                            Swal.close();
                            let title = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';
                            let html = null;
                            if (error.response?.status === 422) {
                                title = 'Validasi gagal. Silakan periksa kembali input Anda.';
                                html = '<ul style="text-align:left; margin:0; padding-left:20px;">' +
                                    Object.values(error.response.data.errors)
                                    .flat()
                                    .map(msg => `<li>${msg}</li>`)
                                    .join('') +
                                    '</ul>';
                            } else if (error.response?.data?.message) {
                                title = error.response.data.message;
                            }
                            Toast.fire({
                                icon: 'error',
                                title: title,
                                html: html
                            });
                        }
                    }
                },

                async init() {
                    Swal.fire({
                        title: 'Memuat data...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    this.contactSelected = this.contacts.find(c => c.id === transaction.contact_id) || null;
                    this.calculate();
                    Swal.close();
                },
            }
        };
    </script>

    <div x-data="receiveModule()" x-init="init()" class="order-page">

        <div>
            <a href="{{ route('finances.cash.show', $account->id) }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali
            </a>
            <h1 class="order-title display">{{ $account->name }} ({{ $account->code }})</h1>
            <div class="order-sub">Terima dana</div>
        </div>

        @include('finance.cash.receive.partials.form')

        <div class="order-form-footer">
            <button class="btn btn-ghost" style="border-style:dashed;" @click="submit('draft')">Simpan Draft</button>
            <button class="btn btn-primary" @click="submit('posted')"><x-misc.icon name="check" :size="14" />Terima
                Dana</button>
        </div>
    </div>
@endsection
