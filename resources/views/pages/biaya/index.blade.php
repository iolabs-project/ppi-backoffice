@extends('layouts.app')
@section('content')
    <script>
        function biayaPageData() {
            return {
                modal: null,
                filterStatus: 'semua',

                biayaAll: @json($biaya),
                biayaPage: 1,
                biayaPerPage: 10,

                get biayaFiltered() {
                    if (this.filterStatus === 'semua') return this.biayaAll;
                    return this.biayaAll.filter(b => b.status === this.filterStatus);
                },
                get biayaPaged() {
                    return this.biayaFiltered.slice(
                        (this.biayaPage - 1) * this.biayaPerPage,
                        this.biayaPage * this.biayaPerPage
                    );
                },
                get biayaTotalPages() {
                    return Math.max(1, Math.ceil(this.biayaFiltered.length / this.biayaPerPage));
                },
                setFilter(s) {
                    this.filterStatus = s;
                    this.biayaPage = 1;
                },

                form: {
                    tanggal: '',
                    keterangan: '',
                    kategori: '',
                    akun: '',
                    jumlah: '',
                },

                fmtRp(n) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
                },

                kategoriColor(k) {
                    const map = {
                        'Gaji':         { bg: '#EEF2FF', fg: '#6366F1' },
                        'Sewa':         { bg: '#FFF7ED', fg: '#EA580C' },
                        'Utilitas':     { bg: '#F0FDF4', fg: '#16A34A' },
                        'Transport':    { bg: '#EFF6FF', fg: '#2563EB' },
                        'Operasional':  { bg: '#FDF4FF', fg: '#9333EA' },
                        'Pemeliharaan': { bg: '#FFFBEB', fg: '#D97706' },
                        'Marketing':    { bg: '#FFF1F2', fg: '#E11D48' },
                    };
                    return map[k] ?? { bg: 'var(--bg-2)', fg: 'var(--ink-3)' };
                },
            };
        }
    </script>

    <div x-data="biayaPageData()" class="biaya-page">

        <div class="order-hd">
            <div>
                <h1 class="order-title display">Biaya</h1>
                <div class="order-sub">Pencatatan dan persetujuan biaya operasional</div>
            </div>
            <div class="order-actions">
                <a href="{{ route('biaya.create') }}" class="btn btn-primary">
                    <x-misc.icon name="plus" :size="14" />Tambah Biaya
                </a>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="biaya-summary">
            <div class="card stat-card stat-card--dark">
                <div class="stat-card__label">Total Biaya Bulan Ini</div>
                <div class="stat-card__value display num">{{ fmt_rp($totalBiaya) }}</div>
                <div class="stat-card__sub">{{ count($biaya) }} entri tercatat</div>
            </div>
            @php
                $disetujui = collect($biaya)->where('status', 'disetujui')->sum('jumlah');
                $menunggu  = collect($biaya)->where('status', 'menunggu')->sum('jumlah');
                $ditolak   = collect($biaya)->where('status', 'ditolak')->sum('jumlah');
            @endphp
            <div class="card stat-card stat-card--good">
                <div class="stat-card__label">Disetujui</div>
                <div class="stat-card__value display num">{{ fmt_rp($disetujui) }}</div>
                <div class="stat-card__sub">{{ collect($biaya)->where('status', 'disetujui')->count() }} entri</div>
            </div>
            <div class="card stat-card stat-card--warn">
                <div class="stat-card__label">Menunggu Persetujuan</div>
                <div class="stat-card__value display num">{{ fmt_rp($menunggu) }}</div>
                <div class="stat-card__sub">{{ collect($biaya)->where('status', 'menunggu')->count() }} entri</div>
            </div>
            <div class="card stat-card stat-card--bad">
                <div class="stat-card__label">Ditolak</div>
                <div class="stat-card__value display num">{{ fmt_rp($ditolak) }}</div>
                <div class="stat-card__sub">{{ collect($biaya)->where('status', 'ditolak')->count() }} entri</div>
            </div>
        </div>

        {{-- Table card --}}
        <div class="card" style="overflow:hidden;">
            <div class="master-toolbar" style="justify-content:space-between;">
                <div class="filter-pills">
                    @foreach ([['semua','Semua'], ['disetujui','Disetujui'], ['menunggu','Menunggu'], ['ditolak','Ditolak']] as [$val, $lbl])
                        <button class="filter-pill" :class="filterStatus === '{{ $val }}' ? 'filter-pill--active' : ''"
                            x-on:click="setFilter('{{ $val }}')">{{ $lbl }}</button>
                    @endforeach
                </div>
                <button class="btn btn-ghost btn-sm">
                    <x-misc.icon name="download" :size="13" />Ekspor
                </button>
            </div>

            <table class="tbl">
                <thead>
                    <tr>
                        <th>No. Biaya</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Kategori</th>
                        <th>Akun</th>
                        <th style="text-align:right;">Jumlah</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="b in biayaPaged" :key="b.id">
                        <tr class="row-tap" style="cursor:pointer;" x-on:click="window.location.href='/biaya/'+b.id">
                            <td class="mono" style="font-weight:600; font-size:12px; color:var(--ink-4);" x-text="b.id"></td>
                            <td style="color:var(--ink-3); white-space:nowrap; font-size:13px;" x-text="b.tanggal"></td>
                            <td style="font-weight:500; font-size:13px;" x-text="b.keterangan"></td>
                            <td>
                                <span class="chip"
                                    :style="'background:' + kategoriColor(b.kategori).bg + '; color:' + kategoriColor(b.kategori).fg"
                                    x-text="b.kategori"></span>
                            </td>
                            <td style="font-size:12px; color:var(--ink-4);" x-text="b.akun"></td>
                            <td class="num" style="text-align:right; font-weight:600; font-size:13px;" x-text="fmtRp(b.jumlah)"></td>
                            <td>
                                <template x-if="b.status === 'disetujui'">
                                    <span class="chip chip-ok"><span class="chip-dot dot-ok"></span>Disetujui</span>
                                </template>
                                <template x-if="b.status === 'menunggu'">
                                    <span class="chip chip-warn"><span class="chip-dot dot-warn"></span>Menunggu</span>
                                </template>
                                <template x-if="b.status === 'ditolak'">
                                    <span class="chip chip-bad"><span class="chip-dot dot-bad"></span>Ditolak</span>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div class="table-pagination">
                <span class="pagination-info"
                    x-text="'Menampilkan ' + Math.min((biayaPage-1)*biayaPerPage+1, biayaFiltered.length) + '–' + Math.min(biayaPage*biayaPerPage, biayaFiltered.length) + ' dari ' + biayaFiltered.length + ' entri'"></span>
                <div class="pagination-controls">
                    <span class="pagination-page-info">Hal. <strong x-text="biayaPage"></strong> / <strong x-text="biayaTotalPages"></strong></span>
                    <button class="btn btn-ghost btn-sm" :disabled="biayaPage <= 1" x-on:click="biayaPage--">
                        <x-misc.icon name="chev-left" :size="14" /> Prev
                    </button>
                    <button class="btn btn-ghost btn-sm" :disabled="biayaPage >= biayaTotalPages" x-on:click="biayaPage++">
                        Next <x-misc.icon name="chev-right" :size="14" />
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection
