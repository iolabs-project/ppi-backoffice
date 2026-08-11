@extends('layouts.app')
@section('content')
    <script>
        function transferModule() {
            return {
                formData: {
                    from_account_id: null,
                    to_account_id: null,
                    subtotal: null,
                    number: '{{ $number }}',
                    transaction_date: "{{ now()->format('Y-m-d') }}",
                    type: 'transfer',
                    description: null,
                },
                account: @json($account),

                accounts: @json($accounts),
                selectedAccount: null,

                // Shorthand: parse masked string to number
                n(v) {
                    return NumberUtils.parseMaskIntoNumeric(v);
                },

                async submit(status) {
                    let isValid = false;
                    if (status === 'draft') {
                        this.formData.status = 'draft';
                        isValid = true;
                    } else if (status === 'posted') {
                        this.formData.status = 'posted';

                        const result = await Swal.fire({
                            title: 'Konfirmasi Transfer Dana',
                            text: 'Apakah anda yakin ingin melakukan transfer dana dengan data yang telah diisi?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, transfer',
                            cancelButtonText: 'Batal',
                            reverseButtons: true,
                        });
                        isValid = result.isConfirmed;
                    }

                    if (isValid) {
                        const titles = {
                            draft: 'Memproses penyimpanan draft transfer dana...',
                            posted: 'Memproses transfer dana...',
                        };
                        Swal.fire({
                            title: titles[status] ?? 'Memproses...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading(),
                        });
                        let body = {
                            ...this.formData,
                            subtotal: this.n(this.formData.subtotal),
                            from_account_id: this.account.id,
                            description: 'Transfer Dana',
                        };
                        try {
                            const response = await axios.post(route('finances.cash.transfer.store', this.account.id),
                                body);
                            Swal.close();
                            Toast.fire({
                                icon: 'success',
                                title: response.data.message
                            });
                            window.location.href = response.data.redirect;
                        } catch (error) {
                            Swal.close();
                            console.error('Error during transfer submission:', error);
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
                }

            };
        }
    </script>
    <div x-data="transferModule()" class="order-page">

        <div>
            <a href="{{ route('finances.cash.show', $account->id) }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali
            </a>
            <h1 class="order-title display">{{ $account->name }} ({{ $account->code }})</h1>
            <div class="order-sub">Kirim dana ke akun lain</div>
        </div>

        @include('finance.cash.transfer.partials.form')

        <div class="order-form-footer">
            <button class="btn btn-ghost" style="border-style:dashed;" @click="submit('draft')">Simpan Draft</button>
            <button class="btn btn-primary" @click="submit('posted')"><x-misc.icon name="check"
                    :size="14" />Transfer</button>
        </div>
    </div>
@endsection
