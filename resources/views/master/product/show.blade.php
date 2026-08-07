@extends('layouts.app')
@section('content')
    @php
        $stok = $produk['stok'] ?? 0;
        $totalJual = array_sum(
            array_map(fn($t) => abs($t['qty']), array_filter($transaksiTerkini, fn($t) => $t['qty'] < 0)),
        );
        $totalBeli = array_sum(
            array_map(fn($t) => $t['qty'], array_filter($transaksiTerkini, fn($t) => $t['qty'] > 0)),
        );
        $stokTotal = array_sum(array_column($stokPerGudang, 'stok'));
        $gudangColors = ['oklch(0.72 0.14 155)', 'oklch(0.72 0.14 220)', 'oklch(0.72 0.14 45)'];

        $runningStok = $stok;
        $pergerakanStokData = [];
        foreach (array_reverse($transaksiTerkini) as $t) {
            $pergerakanStokData[] = [
                'tanggal' => $t['tanggal'],
                'deskripsi' => $t['deskripsi'],
                'harga' => $t['harga'],
                'hargaFmt' => fmt_rp($t['harga']),
                'hppFmt' => fmt_rp($produk['hargaBeli']),
                'qty' => $t['qty'],
                'qtyFmt' => ($t['qty'] > 0 ? '+' : '') . fmt_num($t['qty']),
                'stokFmt' => fmt_num($runningStok) . ' ' . $produk['satuan'],
            ];
            $runningStok -= $t['qty'];
        }

        $terkiniForJs = array_map(
            fn($t) => [
                'tanggal' => $t['tanggal'],
                'deskripsi' => $t['deskripsi'],
                'ref' => $t['ref'],
                'qty' => $t['qty'],
                'qtyFmt' => ($t['qty'] > 0 ? '+' : '') . fmt_num($t['qty']),
                'hargaFmt' => fmt_rp($t['harga']),
                'totalFmt' => fmt_rp($t['total']),
            ],
            $transaksiTerkini,
        );

        $transferForJs = array_map(
            fn($t) => [
                'tanggal' => $t['tanggal'],
                'nomor' => $t['nomor'],
                'dari' => $t['dari'],
                'ke' => $t['ke'],
                'qtyFmt' => fmt_num($t['qty']),
            ],
            $transferGudang,
        );
    @endphp
    <script>
        function productDetailModule() {
            return {
                transactions: [],
                search: {
                    transaction: '',
                },


                m(v) {
                    return NumberUtils.formatNumericIntoMask(v);
                },
                modal: null,
                editProduk: {
                    kode: '{{ $produk['kode'] }}',
                    nama: '{{ addslashes($produk['nama']) }}',
                    kategori: '{{ addslashes($produk['kategori'] ?? '') }}',
                    satuan: '{{ addslashes($produk['satuan']) }}',
                    hargaBeli: {{ $produk['hargaBeli'] }},
                    hargaJual: {{ $produk['hargaJual'] }},
                },
                transaksiTab: 'terkini',

                penyesuaian: {
                    tipe: 'perhitungan',
                    gudang: '',
                    tanggal: '',
                    akun: '8-80100 Penyesuaian Persediaan',
                    nomor: 'SA/00007',
                    qtyAktual: 0,
                    selisih: 0,
                    hargaRataRata: 0,
                },

                terkiniAll: @json($terkiniForJs),
                terkiniPage: 1,
                terkiniPerPage: 10,
                get terkiniPaged() {
                    return this.terkiniAll.slice((this.terkiniPage - 1) * this.terkiniPerPage, this.terkiniPage * this
                        .terkiniPerPage);
                },
                get terkiniTotal() {
                    return Math.ceil(this.terkiniAll.length / this.terkiniPerPage);
                },

                stokAll: @json($pergerakanStokData),
                stokPage: 1,
                stokPerPage: 10,
                get stokPaged() {
                    return this.stokAll.slice((this.stokPage - 1) * this.stokPerPage, this.stokPage * this.stokPerPage);
                },
                get stokTotal() {
                    return Math.ceil(this.stokAll.length / this.stokPerPage);
                },

                transferAll: @json($transferForJs),
                transferPage: 1,
                transferPerPage: 10,
                get transferPaged() {
                    return this.transferAll.slice((this.transferPage - 1) * this.transferPerPage, this.transferPage *
                        this.transferPerPage);
                },
                get transferTotal() {
                    return Math.ceil(this.transferAll.length / this.transferPerPage);
                },
            };
        }

        function productTransactionModule() {
            return {
                search: '',
                tableData: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 10,
                    total: 0,
                    data: []
                },
                loading: false,
                page: 1,
                perPage: 10,
                perPageOptions: [10, 25, 50],

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('master.products.transaction_datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                search: this.search,
                                product_id: '{{ $product->id }}',
                            }
                        });
                        this.tableData = r.data;
                    } catch {
                        Toast.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan saat memuat data.'
                        });
                    } finally {
                        this.loading = false;
                    }
                },
                next() {
                    if (this.page < this.tableData.last_page) {
                        this.page++;
                        this.fetchData();
                    }
                },
                prev() {
                    if (this.page > 1) {
                        this.page--;
                        this.fetchData();
                    }
                },
                handleSearch(q) {
                    this.search = q;
                    this.page = 1;
                    this.fetchData();
                },
            }
        }
    </script>
    <div class="order-page" x-data="productDetailModule()">

        {{-- Header --}}
        <div class="order-hd order-hd--start">
            <div>
                <a href="{{ route('master.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                    <x-misc.icon name="chev-left" :size="13" /> Kembali
                </a>
                <div class="order-title-row">
                    <h1 class="order-title display">{{ $product->name }}</h1>
                    <span class="chip">{{ $product->category->name }}</span>
                    <span class="chip mono" style="font-size:11px;">{{ $product->code }}</span>
                </div>
            </div>
            <div class="order-actions">
                <button class="btn btn-ghost" x-on:click="modal = 'edit_product'"><x-misc.icon name="edit"
                        :size="14" /> Edit Produk</button>
                <button class="btn btn-primary" x-on:click="modal = 'penyesuaian'"><x-misc.icon name="plus"
                        :size="14" /> Penyesuaian Stok</button>
            </div>
        </div>

        {{-- Stat cards --}}
        <div class="produk-stat-grid">
            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:oklch(0.92 0.06 155); color:oklch(0.45 0.14 155);">
                    {{ number_format($stocks->sum('quantity'), 2, '.', ',') }}</div>
                <div class="produk-stat__label">Stok di tangan</div>
                <div class="produk-stat__unit">{{ $product->unit->symbol }}</div>
            </div>
            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:oklch(0.92 0.06 220); color:oklch(0.45 0.14 220);">
                    {{ number_format($soldQty, 2, '.', ',') }}</div>
                <div class="produk-stat__label">Terjual</div>
                <div class="produk-stat__unit">unit · bulan ini</div>
            </div>
            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:oklch(0.92 0.06 45); color:oklch(0.45 0.14 45);">
                    {{ number_format($receivedQty, 2, '.', ',') }}</div>
                <div class="produk-stat__label">Diterima</div>
                <div class="produk-stat__unit">unit · bulan ini</div>
            </div>
            {{-- <div class="card produk-stat">
      <div class="produk-stat__badge" style="background:var(--bg-2); color:var(--ink-2);">{{ number_format($product->average_unit_cost,2,'.',',') }}</div>
      <div class="produk-stat__label">Harga Pokok</div>
      <div class="produk-stat__unit">/ {{ $product->unit->symbol }}</div>
    </div> --}}
        </div>

        {{-- Body: transaksi + sidebar --}}
        <div class="produk-body">

            {{-- Left: transaksi --}}
            <div class="card" style="overflow:hidden;" x-data="productTransactionModule()" x-init="fetchData()">
                <div class="master-toolbar">
                    <div class="master-search">
                        <span class="master-search__icon"><x-misc.icon name="search" :size="14"
                                stroke="var(--ink-4)" /></span>
                        <input class="input master-search__input" placeholder="Cari transaksi untuk produk ini..."
                            x-model="search.transaction" />
                    </div>
                </div>

                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Description</th>
                            <th>No. referensi</th>
                            <th style="text-align:right;">Qty</th>
                            <th style="text-align:right;">Harga</th>
                            <th style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="tableData.data.length === 0">
                            <tr>
                                <td colspan="6"
                                    style="text-align:center; color:var(--ink-4); padding:32px; font-size:13px;">
                                    Belum ada transaksi untuk produk ini
                                </td>
                            </tr>
                        </template>
                        <template x-for="(t,i) in tableData.data" :key="i">
                            <tr>
                                <td style="font-size:13px; color:var(--ink-3);" x-text="t.transaction_date"></td>
                                <td style="font-size:13px;">
                                    <template x-if="t.transaction_type === 'purchase_order'">
                                        <a :href="route('purchasings.purchase_orders.show', t.transaction_id)">
                                            <span x-text="'Pesanan Pembelian'"></span>
                                            <span x-text="t.transaction_number"></span>
                                            <span x-text="t.contact_name"></span>
                                        </a>
                                    </template>
                                    <template x-if="t.transaction_type === 'goods_receipt'">
                                        <a :href="route('purchasings.goods_receipts.show', t.transaction_id)">
                                            <span x-text="'Penerimaan Barang'"></span>
                                            <span x-text="t.transaction_number"></span>
                                            <span x-text="t.contact_name"></span>
                                        </a>
                                    </template>
                                    <template x-if="t.transaction_type === 'purchase_invoice'">
                                        <a :href="route('purchasings.purchase_invoices.show', t.transaction_id)">
                                            <span x-text="'Tagihan Pembelian'"></span>
                                            <span x-text="t.transaction_number"></span>
                                            <span x-text="t.contact_name"></span>
                                        </a>
                                    </template>
                                </td>
                                <td style="font-size:13px; color:var(--ink-4);" x-text="t.transaction_reference_number"></td>
                                <td class="num" style="text-align:right; font-size:13px;" x-text="m(t.quantity)"></td>
                                <td class="num" style="text-align:right; font-size:13px;" x-text="m(t.unit_price)"></td>
                                <td class="num" style="text-align:right; font-size:13px;"
                                    x-text="m(t.total_price)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Right: info sidebar --}}
            <div class="card produk-sidebar">

                <div class="produk-sidebar__section">
                    <div class="produk-sidebar__heading">Informasi</div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">Kategori</span>
                        <span class="chip">{{ $product->category->name }}</span>
                    </div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">Satuan</span>
                        <span class="produk-sidebar__val">{{ $product->unit->name }} ({{ $product->unit->symbol }})</span>
                    </div>
                </div>

                <div class="produk-sidebar__section">
                    <div class="produk-sidebar__heading">Lokasi Gudang</div>
                    @foreach ($stocks as $stock)
                        <div style="margin-bottom:10px;">
                            <div style="display:flex; justify-content:space-between; font-size:12.5px; margin-bottom:4px;">
                                <span
                                    style="color:{{ $gudangColors[$loop->index] ?? 'var(--ink-3)' }}; font-weight:600;">{{ $stock->warehouse->name }}</span>
                                <span class="num"
                                    style="font-size:12px; color:var(--ink-3);">{{ number_format($stock->quantity, 2) }}
                                    {{ $product->unit->symbol }}</span>
                            </div>
                            <div style="height:4px; border-radius:999px; background:var(--line);">
                                <div
                                    style="height:4px; border-radius:999px; background:{{ $gudangColors[$loop->index] ?? 'var(--accent)' }}; width:{{ $stocks->sum('quantity') > 0 ? round(($stock->quantity / $stocks->sum('quantity')) * 100) : 0 }}%;">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>

        {{-- Modal: Edit Produk --}}
        <x-misc.modal title="Edit Produk" show="modal === 'edit_product'" close-handler="modal = null">
            <div class="form-body">
                <div class="form-grid-2">
                    <x-misc.field label="Kode Produk" :required="true">
                        <input class="input mono" x-model="editProduk.kode" />
                    </x-misc.field>
                    <x-misc.field label="Kategori">
                        <input class="input" x-model="editProduk.kategori" placeholder="Tepung, Gula, Minyak..." />
                    </x-misc.field>
                </div>
                <x-misc.field label="Nama Produk" :required="true">
                    <input class="input" x-model="editProduk.nama" />
                </x-misc.field>
                <div class="form-grid-3">
                    <x-misc.field label="Satuan">
                        <input class="input" x-model="editProduk.satuan" placeholder="Sak, Kg, Liter..." />
                    </x-misc.field>
                    <x-misc.field label="Harga Beli">
                        <input class="input num" type="number" style="text-align:right;"
                            x-model="editProduk.hargaBeli" />
                    </x-misc.field>
                    <x-misc.field label="Harga Jual">
                        <input class="input num" type="number" style="text-align:right;"
                            x-model="editProduk.hargaJual" />
                    </x-misc.field>
                </div>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">
                    <x-misc.icon name="x" :size="14" /> Batal
                </button>
                <button class="btn btn-primary">
                    <x-misc.icon name="check" :size="14" /> Simpan Perubahan
                </button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Penyesuaian Stok --}}
        <x-misc.modal title="Penyesuaian Stok" show="modal === 'penyesuaian'" close-handler="modal = null"
            :width="620">
            <div class="form-body">

                {{-- Tipe --}}
                <x-misc.field label="Tipe penyesuaian stok" :required="true">
                    <div class="ps-tipe-group">
                        <label class="ps-tipe-btn"
                            :class="penyesuaian.tipe === 'perhitungan' ? 'ps-tipe-btn--active' : ''">
                            <input type="radio" x-model="penyesuaian.tipe" value="perhitungan"
                                style="display:none;" />
                            <x-misc.icon name="box" :size="14" />
                            Perhitungan Stok
                        </label>
                        <label class="ps-tipe-btn"
                            :class="penyesuaian.tipe === 'masuk_keluar' ? 'ps-tipe-btn--active' : ''">
                            <input type="radio" x-model="penyesuaian.tipe" value="masuk_keluar"
                                style="display:none;" />
                            <x-misc.icon name="swap" :size="14" />
                            Stok Masuk / Keluar
                        </label>
                    </div>
                </x-misc.field>

                {{-- Gudang & Tanggal --}}
                <div class="form-grid-2">
                    <x-misc.field label="Gudang" :required="true">
                        <select class="input" x-model="penyesuaian.gudang">
                            <option value="" disabled>Pilih gudang</option>
                            @foreach ($stokPerGudang as $g)
                                <option value="{{ $g['nama'] }}">{{ $g['nama'] }}</option>
                            @endforeach
                        </select>
                    </x-misc.field>
                    <x-misc.field label="Tanggal" :required="true">
                        <input class="input" type="date" x-model="penyesuaian.tanggal"
                            value="{{ date('Y-m-d') }}" />
                    </x-misc.field>
                </div>

                {{-- Akun & Nomor --}}
                <div class="form-grid-2">
                    <x-misc.field label="Akun" :required="true">
                        <select class="input" x-model="penyesuaian.akun">
                            <option value="8-80100 Penyesuaian Persediaan">8-80100 Penyesuaian Persediaan</option>
                            <option value="1-1300 Persediaan Barang">1-1300 Persediaan Barang</option>
                        </select>
                    </x-misc.field>
                    <x-misc.field label="Nomor">
                        <input class="input mono" x-model="penyesuaian.nomor" placeholder="SA/00001" />
                    </x-misc.field>
                </div>

                {{-- Qty adjustment --}}
                <div class="ps-qty-section">
                    <div class="ps-qty-header">
                        <div>Qty Tercatat</div>
                        <div>Satuan</div>
                        <div>Qty Aktual</div>
                        <div>Selisih</div>
                        <div>Harga Rata-rata</div>
                    </div>
                    <div class="ps-qty-row">
                        {{-- Qty Tercatat: always read-only --}}
                        <div class="ps-qty-cell ps-qty-cell--readonly">{{ fmt_num($stok) }}</div>

                        {{-- Satuan --}}
                        <div class="ps-qty-cell">
                            <select class="input" style="padding:6px 10px; height:36px;">
                                <option>{{ $produk['satuan'] }}</option>
                            </select>
                        </div>

                        {{-- Qty Aktual: editable (perhitungan) / read-only computed (masuk_keluar) --}}
                        <div class="ps-qty-cell">
                            <input class="input num" type="number" style="text-align:right;"
                                x-model="penyesuaian.qtyAktual" x-show="penyesuaian.tipe === 'perhitungan'"
                                placeholder="0" />
                            <div class="ps-qty-cell--readonly" x-show="penyesuaian.tipe === 'masuk_keluar'" x-cloak
                                x-text="{{ $stok }} + Number(penyesuaian.selisih)"></div>
                        </div>

                        {{-- Selisih: read-only computed (perhitungan) / editable (masuk_keluar) --}}
                        <div class="ps-qty-cell">
                            <div class="ps-qty-cell--readonly" x-show="penyesuaian.tipe === 'perhitungan'"
                                x-text="Number(penyesuaian.qtyAktual) - {{ $stok }}"></div>
                            <input class="input num" type="number" style="text-align:right;"
                                x-model="penyesuaian.selisih" x-show="penyesuaian.tipe === 'masuk_keluar'" x-cloak
                                placeholder="0" />
                        </div>

                        {{-- Harga Rata-rata: always editable --}}
                        <div class="ps-qty-cell">
                            <input class="input num" type="number" style="text-align:right;"
                                x-model="penyesuaian.hargaRataRata" placeholder="0" />
                        </div>
                    </div>
                </div>

            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">
                    <x-misc.icon name="x" :size="14" /> Batal
                </button>
                <button class="btn btn-primary">
                    <x-misc.icon name="check" :size="14" /> Simpan
                </button>
            </x-slot:footer>
        </x-misc.modal>

    </div>
@endsection
