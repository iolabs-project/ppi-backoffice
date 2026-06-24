@extends('layouts.app')
@section('content')
    <script>
        function purchaseOrderForm() {
            return {
                formData: {
                    customer_id: null,
                    warehouse_id: null,
                    sales_person_id: null,
                    number: '{{ $number }}',
                    order_date: "{{ now()->format('Y-m-d') }}",
                    due_date: "{{ now()->addDays(14)->format('Y-m-d') }}",
                    discount_amount: 0,
                    transport_cost: 0,
                    other_cost: 0
                },
                // Customer Options
                customers: [],
                customerLoading: false,
                customerSearch: '',
                customerSelected: null,
                customerOpen: false,
                // Warehouse Options
                warehouses: [],
                warehouseLoading: false,
                warehouseSearch: '',
                warehouseSelected: null,
                warehouseOpen: false,
                // Sales Options
                salesPersons: [],
                salesPersonLoading: false,
                salesPersonSearch: '',
                salesPersonSelected: null,
                salesPersonOpen: false,
                // Payment Terms
                paymentTerms: @json($paymentTerms),
                paymentTermSelected: null,
                paymentTermOpen: false,

                produkList: @json($produk),
                items: [{
                        kode: 'TPG-001',
                        nama: 'Tepung Terigu Cakra Kembar',
                        qty: 120,
                        satuan: 'Kg',
                        harga: 215000
                    },
                    {
                        kode: 'GLP-002',
                        nama: 'Gula Pasir Kemasan Premium',
                        qty: 40,
                        satuan: 'Kg',
                        harga: 678000
                    },
                ],
                diskon: 2500000,
                ongkir: 1800000,
                biayaLain: 0,
                get subtotal() {
                    return this.items.reduce((s, i) => s + i.qty * i.harga, 0);
                },
                get total() {
                    return this.subtotal - this.diskon + this.ongkir + this.biayaLain;
                },
                addItem() {
                    this.items.push({
                        kode: '',
                        nama: '',
                        qty: 1,
                        satuan: '',
                        harga: 0
                    });
                },
                removeItem(idx) {
                    if (this.items.length > 1) this.items.splice(idx, 1);
                },
                fmt(n) {
                    return 'Rp ' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                },
                parseNum(str) {
                    return Number(String(str).replace(/[^0-9]/g, '')) || 0;
                },
                fmtNum(n) {
                    return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                },
                fmtInput(e) {
                    let el = e.target;
                    let pos = el.value.slice(0, el.selectionStart).replace(/[^0-9]/g, '').length;
                    let raw = el.value.replace(/[^0-9]/g, '');
                    el.value = raw ? raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
                    let i = 0,
                        c = 0;
                    while (i < el.value.length && c < pos) {
                        if (/\d/.test(el.value[i])) c++;
                        i++;
                    }
                    el.setSelectionRange(i, i);
                },
                initials(name) {
                    return name ? name.split(' ').slice(0, 2).map(w => w[0]).join('') : '?';
                },

                async loadCustomers() {
                    this.customerLoading = true;

                    try {
                        const response = await axios.get(
                            route('master.contacts.options'), {
                                params: {
                                    search: this.customerSearch,
                                    type: 'customer'
                                }
                            }
                        );

                        this.customers = response.data.data;


                    } finally {
                        this.customerLoading = false;
                    }
                },

                async loadSalesPersons() {
                    this.salesPersonLoading = true;

                    try {
                        const response = await axios.get(
                            route('master.contacts.options'), {
                                params: {
                                    search: this.salesPersonSearch,
                                    type: 'employee'
                                }
                            }
                        );

                        this.salesPersons = response.data.data;


                    } finally {
                        this.salesPersonLoading = false;
                    }
                },

                async loadWarehouses() {
                    this.warehouseLoading = true;

                    try {
                        const response = await axios.get(
                            route('master.warehouses.options'), {
                                params: {
                                    search: this.warehouseSearch,
                                }
                            }
                        );

                        this.warehouses = response.data.data;


                    } finally {
                        this.warehouseLoading = false;
                    }
                },

                async init() {
                    await this.loadCustomers();
                    await this.loadSalesPersons();
                    await this.loadWarehouses();
                }

            };
        }
    </script>

    <div x-data="purchaseOrderForm()" x-init="init()" class="order-page">

        <div>
            <a href="{{ route('purchasings.purchasing_orders.index') }}" class="btn btn-ghost btn-sm"
                style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali
            </a>
            <h1 class="order-title display">Tambah Sales Order</h1>
            <div class="order-sub">Buat dokumen SO baru. Stok akan dipotong otomatis saat pengiriman dibuat.</div>
        </div>

        {{-- Info Order --}}
        <div class="card card-bd--form">
            <div class="display card-hd-title">Informasi Order</div>
            <div class="order-form-grid-4">

                {{-- Customer Dropdown --}}
                <x-misc.field label="Customer" :required="true">
                    <div class="dropdown-wrap" @click.outside="customerOpen=false">
                        <div class="input dropdown-trigger" @click="customerOpen=!customerOpen">
                            <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                                x-text="initials(customerSelected ? customerSelected.name : '')"></div>
                            <span style="flex:1; font-weight:500;"
                                x-text="customerSelected ? customerSelected.name : 'Pilih Customer'"></span>
                            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
                        </div>
                        <div class="dropdown-menu" x-show="customerOpen" x-cloak>
                            <template x-for="c in customers" :key="c.id">
                                <div class="dropdown-item"
                                    @click="customerSelected=c; formData.customer_id=c.id; customerOpen=false">
                                    <div class="avatar"
                                        style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                                        x-text="initials(c.name)"></div>
                                    <span x-text="c.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </x-misc.field>

                {{-- Nomor PO --}}
                <x-misc.field label="Nomor PO" :required="true">
                    <input class="input mono" x-model="formData.number" />
                </x-misc.field>

                {{-- Tanggal --}}
                <x-misc.field label="Tanggal" :required="true">
                    <input type="date" class="input" x-model="formData.order_date" />
                </x-misc.field>

                {{-- Jatuh Tempo --}}
                <x-misc.field label="Jatuh Tempo" :required="true">
                    <input type="date" class="input" x-model="formData.due_date" />
                </x-misc.field>

                {{-- Gudang Dropdown --}}
                <x-misc.field label="Gudang" :required="true">
                    <div class="dropdown-wrap" @click.outside="warehouseOpen=false">
                        <div class="input dropdown-trigger" @click="warehouseOpen=!warehouseOpen">
                            <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                                x-text="initials(warehouseSelected ? warehouseSelected.name : '')"></div>
                            <span style="flex:1; font-weight:500;"
                                x-text="warehouseSelected ? warehouseSelected.name : 'Pilih Gudang'"></span>
                            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
                        </div>
                        <div class="dropdown-menu" x-show="warehouseOpen" x-cloak>
                            <template x-for="g in warehouses" :key="g.id">
                                <div class="dropdown-item"
                                    @click="warehouseSelected=g; formData.warehouse_id=g.id; warehouseOpen=false">
                                    <div class="avatar"
                                        style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                                        x-text="initials(g.name)"></div>
                                    <span x-text="g.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </x-misc.field>

                {{-- Sales Person Dropdown --}}
                <x-misc.field label="Sales Person">
                    <div class="dropdown-wrap" @click.outside="salesPersonOpen=false">
                        <div class="input dropdown-trigger" @click="salesPersonOpen=!salesPersonOpen">
                            <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                                x-text="initials(salesPersonSelected ? salesPersonSelected.name : '')"></div>
                            <span style="flex:1; font-weight:500;"
                                x-text="salesPersonSelected ? salesPersonSelected.name : 'Pilih Sales Person'"></span>
                            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
                        </div>
                        <div class="dropdown-menu" x-show="salesPersonOpen" x-cloak>
                            <template x-for="s in salesPersons" :key="s.id">
                                <div class="dropdown-item"
                                    @click="salesPersonSelected=s; formData.sales_person_id=s.id; salesPersonOpen=false">
                                    <div class="avatar"
                                        style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                                        x-text="initials(s.name)"></div>
                                    <span x-text="s.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </x-misc.field>

                {{-- Termin Pembayaran Dropdown --}}
                <x-misc.field label="Termin Pembayaran">
                    <div class="dropdown-wrap" @click.outside="paymentTermOpen=false">
                        <div class="input dropdown-trigger" @click="paymentTermOpen=!paymentTermOpen">
                            <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                                x-text="initials(paymentTermSelected ? paymentTermSelected.name : '')"></div>
                            <span style="flex:1; font-weight:500;"
                                x-text="paymentTermSelected ? paymentTermSelected.name : 'Pilih Termin Pembayaran'"></span>
                            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
                        </div>
                        <div class="dropdown-menu" x-show="paymentTermOpen" x-cloak>
                            <template x-for="t in paymentTerms" :key="t.id">
                                <div class="dropdown-item"
                                    @click="paymentTermSelected=t; formData.payment_term_id=t.id; paymentTermOpen=false">
                                    <span x-text="t.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </x-misc.field>

                {{-- Nomor Referensi --}}
                <x-misc.field label="Nomor Referensi">
                    <input class="input mono" placeholder="(opsional)" />
                </x-misc.field>

            </div>
        </div>

        {{-- Items --}}
        <div class="card" style="overflow:visible;">
            <div class="card-hd">
                <div class="display card-hd-title">Daftar Produk</div>
                <button class="btn btn-ghost btn-sm" x-on:click="addItem()">
                    <x-misc.icon name="plus" :size="13" />Tambah Produk
                </button>
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:48px;">#</th>
                        <th>Pilih Produk</th>
                        <th style="width:120px; text-align:right;">Qty</th>
                        <th style="width:140px;">Satuan</th>
                        <th style="width:160px; text-align:right;">Harga</th>
                        <th style="width:160px; text-align:right;">Subtotal</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(it, i) in items" :key="i">
                        <tr x-data="{ open: false }">
                            <td class="mono" style="color:var(--ink-4);" x-text="String(i+1).padStart(2,'0')"></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div class="product-icon">
                                        <x-misc.icon name="box" :size="16" stroke="var(--ink-3)" />
                                    </div>
                                    <div style="flex:1;" class="dropdown-wrap" @click.outside="open=false">
                                        <div class="input dropdown-trigger" style="height:32px; padding:0 10px;"
                                            @click="open=!open">
                                            <span
                                                style="flex:1; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;"
                                                :style="it.nama ? '' : 'color:var(--ink-4);'"
                                                x-text="it.nama || 'Pilih Produk'"></span>
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                stroke="var(--ink-4)" stroke-width="1.6" stroke-linecap="round"
                                                stroke-linejoin="round" style="flex-shrink:0;">
                                                <path d="m6 9 6 6 6-6" />
                                            </svg>
                                        </div>
                                        <div class="mono" style="font-size:11px; color:var(--ink-4); margin-top:3px;"
                                            x-text="it.kode || '— belum dipilih'"></div>
                                        <div class="dropdown-menu" x-show="open" x-cloak style="min-width:320px;">
                                            <template x-for="p in produkList" :key="p.kode">
                                                <div class="dropdown-item"
                                                    @click="it.nama=p.nama; it.kode=p.kode; it.satuan=p.satuan; it.harga=p.hargaJual; open=false">
                                                    <div style="flex:1; min-width:0;">
                                                        <div style="font-size:13px;" x-text="p.nama"></div>
                                                        <div class="mono" style="font-size:11px; color:var(--ink-4);"
                                                            x-text="p.kode"></div>
                                                    </div>
                                                    <span class="dropdown-item__sub" x-text="p.satuan"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input class="input num" style="height:32px; text-align:right;" :value="fmtNum(it.qty)"
                                    @focus="$event.target.select()"
                                    @input="fmtInput($event); it.qty = parseNum($event.target.value)" />
                            </td>
                            <td>
                                <div class="input input--readonly"
                                    style="height:32px; display:flex; align-items:center; padding:0 10px; color:var(--ink-3);">
                                    <span x-text="it.satuan || '—'"></span>
                                </div>
                            </td>
                            <td>
                                <input class="input num" style="height:32px; text-align:right;" :value="fmtNum(it.harga)"
                                    @focus="$event.target.select()"
                                    @input="fmtInput($event); it.harga = parseNum($event.target.value)" />
                            </td>
                            <td class="num" style="text-align:right; font-weight:600;"
                                x-text="fmt(it.qty * it.harga)"></td>
                            <td>
                                <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                    :disabled="items.length <= 1"
                                    :style="items.length <= 1 ? 'opacity:0.25; cursor:not-allowed;' : ''"
                                    x-on:click="removeItem(i)">
                                    <x-misc.icon name="trash" :size="14" stroke="var(--ink-4)" />
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div class="order-items-split">
                <div class="order-extras">
                    <div class="display order-extras__title">Biaya Tambahan</div>
                    <div class="order-extras__grid-3">
                        <x-misc.field label="Diskon">
                            {{-- <input class="input num" style="text-align:right;" :value="fmtNum(diskon)"
                                @focus="$event.target.select()"
                                @input="fmtInput($event); diskon = parseNum($event.target.value)" /> --}}

                            <input class="input num" style="text-align:right;" x-model="formData.discount_amount"
                                x-mask:dynamic="$money($input, ',')" />
                        </x-misc.field>
                        <x-misc.field label="Ongkos Kirim">
                            <input class="input num" style="text-align:right;" :value="fmtNum(ongkir)"
                                @focus="$event.target.select()"
                                @input="fmtInput($event); ongkir = parseNum($event.target.value)" />
                        </x-misc.field>
                        <x-misc.field label="Biaya Lain-lain">
                            <input class="input num" style="text-align:right;" :value="fmtNum(biayaLain)"
                                @focus="$event.target.select()"
                                @input="fmtInput($event); biayaLain = parseNum($event.target.value)" />
                        </x-misc.field>
                    </div>
                    <x-misc.field label="Catatan Internal">
                        <textarea class="input" rows="2" placeholder="Tulis catatan untuk tim gudang/pengiriman…"></textarea>
                    </x-misc.field>
                </div>
                <div class="order-summary">
                    <div class="display order-summary__title">Ringkasan</div>
                    <div class="order-summary__row">
                        <span class="order-summary__label">Subtotal</span>
                        <span class="num" style="font-weight:500;" x-text="fmt(subtotal)"></span>
                    </div>
                    <div class="order-summary__row">
                        <span class="order-summary__label">Diskon</span>
                        {{-- <span class="num" style="font-weight:500;" x-text="'–' + fmt(diskon)"></span> --}}
                        <span style="font-weight:500;">-<span class="num" style="font-weight:500;"
                                x-text="formData.discount_amount"></span></span>
                    </div>
                    <div class="order-summary__row">
                        <span class="order-summary__label">Ongkos Kirim</span>
                        <span class="num" style="font-weight:500;" x-text="fmt(ongkir)"></span>
                    </div>
                    <div class="order-summary__row">
                        <span class="order-summary__label">Biaya Lain-lain</span>
                        <span class="num" style="font-weight:500;" x-text="fmt(biayaLain)"></span>
                    </div>
                    <div class="order-summary__divider"></div>
                    <div class="order-summary__total">
                        <span class="order-summary__total-label">Total Harga</span>
                        <span class="order-summary__total-value display num" x-text="fmt(total)"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="order-form-footer">
            <a href="{{ route('penjualan.index') }}" class="btn btn-ghost">Batal</a>
            <button class="btn btn-ghost" style="border-style:dashed;">Simpan Draft</button>
            <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan SO</button>
        </div>

    </div>
@endsection
