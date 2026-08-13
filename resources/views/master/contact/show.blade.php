@extends('layouts.app')
@section('content')
    <script>
        function contactDetailModule() {
            return {
                n(v) {
                    return NumberUtils.parseMaskIntoNumeric(v);
                },
                modal: null,
                contact: @json($contact),
                form: {
                    id: null,
                    code: '',
                    name: '',
                    email: '',
                    phone: '',
                    address: '',
                    city: '',
                    state: '',
                    postal_code: '',
                    note: '',
                    is_customer: true,
                    is_supplier: false,
                    is_employee: false,
                    transportation_cost: 0,
                },

                openEditModal() {
                    this.form = {
                        id: this.contact.id,
                        code: this.contact.code,
                        name: this.contact.name,
                        email: this.contact.email,
                        phone: this.contact.phone,
                        address: this.contact.address,
                        city: this.contact.city,
                        state: this.contact.state,
                        postal_code: this.contact.postal_code,
                        note: this.contact.note,
                        is_customer: !!this.contact.is_customer,
                        is_supplier: !!this.contact.is_supplier,
                        is_employee: !!this.contact.is_employee,
                        transportation_cost: this.contact.transportation_cost,
                    };
                    this.modal = 'edit_contact';
                },

                avatarMeta(name) {
                    const words = (name || '').trim().split(/\s+/).filter(Boolean);
                    const initials = words.slice(0, 2).map(w => w[0].toUpperCase()).join('');
                    let h = 0;
                    for (const c of (name || '')) h = ((h * 31) + c.charCodeAt(0)) & 0xFFFFFFFF;
                    const hue = ((h % 360) + 360) % 360;
                    return {
                        initials,
                        bg: 'oklch(0.92 0.04 ' + hue + ')',
                        fg: 'oklch(0.45 0.10 ' + hue + ')'
                    };
                },

                async handleUpdate() {
                    Swal.fire({
                        title: 'Konfirmasi Perubahan Kontak',
                        text: 'Apakah anda yakin ingin memperbarui kontak dengan data yang telah diisi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, perbarui kontak',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            let body = {
                                ...this.form,
                            };
                            body.transportation_cost = this.n(body.transportation_cost);

                            Swal.fire({
                                title: 'Memproses perubahan Kontak...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            try {
                                const response = await axios.put(
                                    route('master.contacts.update', this.form.id), body
                                );
                                this.modal = null;
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                
                                window.location.reload();
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
                    });
                }
            };
        }
    </script>
    @php
        $isEmployee = $contact->is_employee;
        $isCustomer = $contact->is_customer;
        $isSupplier = $contact->is_supplier;

        $words = array_filter(explode(' ', $contact->name));
        $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice($words, 0, 2)));
        $h = 0;
        foreach (str_split($contact->name) as $c) {
            $h = ($h * 31 + ord($c)) & 0xffffffff;
        }
        $hue = $h % 360;
        $avatarBg = "oklch(0.92 0.04 {$hue})";
        $avatarFg = "oklch(0.45 0.10 {$hue})";
    @endphp
    <div class="order-page" x-data="contactDetailModule()">

        {{-- Header --}}
        <div class="order-hd order-hd--start">
            <div>
                <a href="{{ route('master.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                    <x-misc.icon name="chev-left" :size="13" /> Kembali
                </a>
                <div style="display:flex; align-items:center; gap:14px;">
                    {{-- <div class="avatar"
                        style="width:52px; height:52px; font-size:16px; border-radius:14px;
             background:{{ $avatarBg }}; color:{{ $avatarFg }};">
                        {{ $initials }}</div> --}}
                    <div class="avatar"
                        :style="'background:' + avatarMeta(contact.name).bg + '; color:' + avatarMeta(contact.name).fg +
                            '; width:52px; height:52px; font-size:16px; border-radius:14px;'">
                        <span x-text="avatarMeta(contact.name).initials"></span>
                    </div>
                    <div>
                        <div class="order-title-row">
                            <h1 class="order-title display">{{ $contact->name }}</h1>
                        </div>
                        <div class="order-sub">{{ $contact->code }}</div>
                    </div>
                </div>
            </div>
            <div class="order-actions">
                <button class="btn btn-ghost" x-on:click="openEditModal()">
                    <x-misc.icon name="edit" :size="14" /> Edit Kontak
                </button>
            </div>
        </div>

        {{-- Stat cards --}}
        <div class="produk-stat-grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:oklch(0.92 0.06 220); color:oklch(0.45 0.14 220);">
                    {{ 0 }}
                </div>
                <div class="produk-stat__label">Transaksi</div>
                <div class="produk-stat__unit">Tercatat bulan ini</div>
            </div>

            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:var(--bad-bg); color:var(--bad);">
                    {{ fmt_rp_short(0) }}
                </div>
                <div class="produk-stat__label">Hutang (Anda)</div>
                <div class="produk-stat__unit">Outstanding bulan ini</div>
            </div>

            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:oklch(0.92 0.06 155); color:oklch(0.45 0.14 155);">
                    {{ fmt_rp_short(0) }}
                </div>
                <div class="produk-stat__label">Hutang (Kontak)</div>
                <div class="produk-stat__unit">Outstanding bulan ini</div>
            </div>


        </div>

        {{-- Body --}}
        <div class="produk-body">

            {{-- Left: transaksi + chart --}}
            <div style="display:grid; gap:16px;">

                {{-- Transaksi table --}}
                <div class="card" style="overflow:hidden;">
                    <div class="card-hd">
                        <div class="display card-hd-title">Riwayat Transaksi</div>
                    </div>
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nomor</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th style="text-align:right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- TODO: implement contact transaction datatable --}}
                        </tbody>
                    </table>
                    
                </div>
            </div>

            {{-- Right: info sidebar --}}
            <div class="card produk-sidebar">

                <div class="produk-sidebar__section">
                    <div class="produk-sidebar__heading">Informasi Kontak</div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">Tipe</span>
                        <span>
                            @if ($isEmployee)
                                <span class="chip chip-accent">Karyawan</span>
                            @endif
                            @if ($isSupplier)
                                <span class="chip chip-accent">Supplier</span>
                            @endif
                            @if ($isCustomer)
                                <span class="chip chip-accent">Customer</span>
                            @endif
                        </span>
                    </div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">Email</span>
                        <span class="produk-sidebar__val"
                            style="font-size:12px; color:var(--accent);">{{ $contact->email ?? '-' }}</span>
                    </div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">Telepon</span>
                        <span class="produk-sidebar__val" style="font-size:12.5px;">{{ $contact->phone ?? '-' }}</span>
                    </div>
                </div>
                <div class="produk-sidebar__section">
                    <div class="produk-sidebar__heading">Alamat</div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">Alamat</span>
                        <span class="produk-sidebar__val"
                            style="font-size:12px; color:var(--accent);">{{ $contact->address ?? '-' }}</span>
                    </div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">Provinsi</span>
                        <span class="produk-sidebar__val"
                            style="font-size:12px; color:var(--accent);">{{ $contact->state ?? '-' }}</span>
                    </div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">Kota</span>
                        <span class="produk-sidebar__val"
                            style="font-size:12px; color:var(--accent);">{{ $contact->city ?? '-' }}</span>
                    </div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">Kode Pos</span>
                        <span class="produk-sidebar__val"
                            style="font-size:12px; color:var(--accent);">{{ $contact->postal_code ?? '-' }}</span>
                    </div>
                </div>

                <div class="produk-sidebar__section">
                    <div class="produk-sidebar__heading">Biaya (Penjualan)</div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">Biaya Transportasi</span>
                        <span class="num produk-sidebar__val"
                            style="color:var(--bad); font-weight:700;">{{ number_format($contact->transportation_cost, 2) }}</span>
                    </div>
                </div>


            </div>
        </div>

        @include('master.partials.modals.contact-modal')

    </div>
@endsection
