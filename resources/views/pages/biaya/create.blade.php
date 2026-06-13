@extends('layouts.app')
@section('content')
    <script>
        function biayaCreateData() {
            return {
                bayarNanti: false,
                akunOpen: false,
                penerimaOpen: false,

                selectedAkun: null,
                selectedPenerima: null,

                akunList: @json($akunKas),
                penerimaList: @json($kontak),
                coaList: @json($coa),

                items: [{
                    akunOpen: false,
                    selectedAkun: null,
                    deskripsi: '',
                    jumlah: 0
                }],

                pemotongan: 0,
                showPemotongan: false,
                catatan: '',

                get subtotal() {
                    return this.items.reduce((s, i) => s + (Number(i.jumlah) || 0), 0);
                },
                get total() {
                    return this.subtotal - (Number(this.pemotongan) || 0);
                },

                addItem() {
                    this.items.push({
                        akunOpen: false,
                        selectedAkun: null,
                        deskripsi: '',
                        jumlah: 0
                    });
                },
                removeItem(idx) {
                    if (this.items.length > 1) this.items.splice(idx, 1);
                },

                fmt(n) {
                    return 'Rp ' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                },
                fmtNum(n) {
                    return Math.round(n || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                },
                parseNum(str) {
                    return Number(String(str).replace(/[^0-9]/g, '')) || 0;
                },
                fmtInput(e, obj, key) {
                    let el = e.target;
                    let raw = el.value.replace(/[^0-9]/g, '');
                    el.value = raw ? raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
                    obj[key] = Number(raw) || 0;
                },

                initials(name) {
                    return name ? name.trim().split(/\s+/).slice(0, 2).map(w => w[0].toUpperCase()).join('') : '?';
                },
            };
        }
    </script>

    <div x-data="biayaCreateData()" class="order-page">

        {{-- Page header --}}
        <div>
            <a href="{{ route('biaya.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali
            </a>
            <h1 class="order-title display">Tambah Biaya</h1>
            <div class="order-sub">Catat pengeluaran operasional dan alokasikan ke akun beban yang sesuai.</div>
        </div>

        {{-- Informasi Pembayaran --}}
        <div class="card card-bd--form">
            <div class="display card-hd-title" style="margin-bottom:4px;">Informasi Pembayaran</div>

            {{-- Dibayar Dari + Bayar Nanti --}}
            <div style="display:flex; align-items:flex-end; gap:16px;">
                <div style="flex:1;" x-show="!bayarNanti">
                    <x-misc.field label="Dibayar Dari" :required="true">
                        <div class="dropdown-wrap" @click.outside="akunOpen=false">
                            <div class="input dropdown-trigger" @click="akunOpen=!akunOpen">
                                <x-misc.icon name="dollar" :size="15" stroke="var(--ink-4)" />
                                <span style="flex:1;"
                                    x-text="selectedAkun ? selectedAkun.nama : 'Pilih akun kas / bank'"></span>
                                <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
                            </div>
                            <div class="dropdown-menu" x-show="akunOpen" x-cloak>
                                <template x-for="a in akunList" :key="a.id">
                                    <div class="dropdown-item" @click="selectedAkun=a; akunOpen=false">
                                        <x-misc.icon name="dollar" :size="14" stroke="var(--ink-4)" />
                                        <div style="flex:1; min-width:0;">
                                            <div style="font-size:13px;" x-text="a.nama"></div>
                                            <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="a.id">
                                            </div>
                                        </div>
                                        <span class="dropdown-item__sub num"
                                            x-text="'Rp ' + a.saldo.toLocaleString('id-ID')"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </x-misc.field>
                </div>
                <div style="flex:1;" x-show="bayarNanti" x-cloak>
                    <div class="input input--readonly"
                        style="display:flex; align-items:center; gap:8px; color:var(--ink-4);">
                        <x-misc.icon name="receipt" :size="15" stroke="var(--ink-4)" />
                        <span style="font-size:13px;">Dicatat sebagai hutang — belum dipotong saldo</span>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:10px; padding-bottom:2px; flex-shrink:0;">
                    <button type="button" class="biaya-toggle" :class="bayarNanti ? 'biaya-toggle--on' : ''"
                        @click="bayarNanti = !bayarNanti" style="flex-shrink:0;">
                        <span class="biaya-toggle__thumb"></span>
                    </button>
                    <span style="font-size:13px; font-weight:500; color:var(--ink-3); white-space:nowrap;">Bayar
                        Nanti</span>
                </div>
            </div>

            {{-- Penerima --}}
            <x-misc.field label="Penerima">
                <div class="dropdown-wrap" @click.outside="penerimaOpen=false">
                    <div class="input dropdown-trigger" @click="penerimaOpen=!penerimaOpen">
                        <div class="avatar"
                            style="width:26px;height:26px;font-size:10px;background:var(--bg-3);color:var(--ink-2);"
                            x-text="initials(selectedPenerima ? selectedPenerima.nama : '')"></div>
                        <span style="flex:1;" x-text="selectedPenerima ? selectedPenerima.nama : 'Pilih penerima'"></span>
                        <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
                    </div>
                    <div class="dropdown-menu" x-show="penerimaOpen" x-cloak>
                        <template x-for="k in penerimaList" :key="k.id">
                            <div class="dropdown-item" @click="selectedPenerima=k; penerimaOpen=false">
                                <div class="avatar"
                                    style="width:26px;height:26px;font-size:10px;background:var(--bg-3);color:var(--ink-2);"
                                    x-text="initials(k.nama)"></div>
                                <div style="flex:1; min-width:0;">
                                    <div style="font-size:13px;" x-text="k.nama"></div>
                                    <div style="font-size:11px; color:var(--ink-4);" x-text="k.kota"></div>
                                </div>
                                <span class="dropdown-item__sub" x-text="k.tipe"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </x-misc.field>

            {{-- Tanggal + Nomor --}}
            <div class="form-grid-2">
                <x-misc.field label="Tgl. Transaksi" :required="true">
                    <input type="date" class="input" value="{{ now()->format('Y-m-d') }}" />
                </x-misc.field>
                <x-misc.field label="Nomor">
                    <div style="position:relative;">
                        <input class="input mono" value="EXP/{{ str_pad(rand(1, 999), 5, '0', STR_PAD_LEFT) }}" />
                        <span class="auto-tag"
                            style="position:absolute; right:12px; top:50%; transform:translateY(-50%);">Auto</span>
                    </div>
                </x-misc.field>
            </div>

        </div>

        {{-- Rincian Biaya --}}
        <div class="card" style="overflow:visible;">
            <div class="card-hd">
                <div class="display card-hd-title">Rincian Biaya</div>
                <button type="button" class="btn btn-ghost btn-sm" x-on:click="addItem()">
                    <x-misc.icon name="plus" :size="13" />Tambah Baris
                </button>
            </div>

            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:44px;">#</th>
                        <th style="min-width:220px;">Akun Biaya</th>
                        <th>Deskripsi</th>
                        <th style="width:180px; text-align:right;">Jumlah</th>
                        <th style="width:44px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(it, i) in items" :key="i">
                        <tr>
                            <td class="mono" style="color:var(--ink-4); font-size:12px;"
                                x-text="String(i+1).padStart(2,'0')"></td>

                            {{-- Akun Biaya dropdown --}}
                            <td>
                                <div class="dropdown-wrap" @click.outside="it.akunOpen=false">
                                    <div class="input dropdown-trigger" style="height:34px; padding:0 10px;"
                                        @click="it.akunOpen=!it.akunOpen">
                                        <span style="flex:1; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;"
                                            :style="it.selectedAkun ? '' : 'color:var(--ink-4);'"
                                            x-text="it.selectedAkun ? it.selectedAkun.nama : 'Pilih akun'"></span>
                                        <x-misc.icon name="chev-down" :size="13" stroke="var(--ink-4)" />
                                    </div>
                                    <div class="dropdown-menu" x-show="it.akunOpen" x-cloak style="min-width:280px;">
                                        <template x-for="c in coaList" :key="c.kode">
                                            <div class="dropdown-item" @click="it.selectedAkun=c; it.akunOpen=false">
                                                <div style="flex:1; min-width:0;">
                                                    <div style="font-size:13px;" x-text="c.nama"></div>
                                                    <div class="mono" style="font-size:11px; color:var(--ink-4);"
                                                        x-text="c.kode"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </td>

                            {{-- Deskripsi --}}
                            <td>
                                <input class="input" style="height:34px;" placeholder="Deskripsi singkat..."
                                    x-model="it.deskripsi" />
                            </td>

                            {{-- Jumlah --}}
                            <td>
                                <input class="input num" style="height:34px; text-align:right;" :value="fmtNum(it.jumlah)"
                                    @focus="$event.target.select()" @input="fmtInput($event, it, 'jumlah')" />
                            </td>

                            {{-- Delete --}}
                            <td>
                                <button type="button" class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                    :disabled="items.length <= 1"
                                    :style="items.length <= 1 ? 'opacity:0.25; cursor:not-allowed;' : ''"
                                    x-on:click="removeItem(i)">
                                    <x-misc.icon name="trash" :size="14" stroke="var(--bad)" />
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            {{-- Split: Catatan | Ringkasan --}}
            <div class="order-items-split">

                {{-- Catatan --}}
                <div class="order-extras">
                    <div class="display order-extras__title">Catatan</div>
                    <textarea class="input" rows="4" placeholder="Tulis catatan internal..." x-model="catatan"></textarea>
                </div>

                {{-- Summary --}}
                <div class="order-summary">
                    <div class="display order-summary__title">Ringkasan</div>

                    <div class="order-summary__row">
                        <span class="order-summary__label">Sub Total</span>
                        <span class="num" style="font-weight:500;" x-text="fmt(subtotal)"></span>
                    </div>

                    <template x-if="showPemotongan">
                        <div class="order-summary__row" style="align-items:center; gap:8px;">
                            <span class="order-summary__label">Pemotongan</span>
                            <input class="input num" style="height:30px; text-align:right; width:120px; font-size:13px;"
                                :value="fmtNum(pemotongan)" @focus="$event.target.select()"
                                @input="fmtInput($event, $data, 'pemotongan')" />
                        </div>
                    </template>

                    <div class="order-summary__divider"></div>
                    <div class="order-summary__total">
                        <span class="order-summary__total-label">Total</span>
                        <span class="order-summary__total-value display num" x-text="fmt(total)"></span>
                    </div>

                    <button type="button" class="btn btn-ghost btn-sm"
                        style="justify-content:flex-start; color:var(--accent); border:none; padding:0; font-size:12px;"
                        x-show="!showPemotongan" @click="showPemotongan=true">
                        + Pemotongan
                    </button>
                </div>

            </div>
        </div>

        <div class="order-form-footer">
            <a href="{{ route('biaya.index') }}" class="btn btn-ghost">Batal</a>
            <button type="button" class="btn btn-primary">
                <x-misc.icon name="check" :size="14" />Tambah Biaya
            </button>
        </div>

    </div>
@endsection
