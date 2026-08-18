{{-- =================== JURNAL UMUM =================== --}}
<div class="card" style="overflow:hidden;" x-data="journalModule()" x-init="init()">
    <div class="card-hd">
        <div class="display card-hd-title">Jurnal Umum</div>
        <button class="btn btn-ghost btn-sm"><x-misc.icon name="download" :size="13" />Ekspor</button>
    </div>
    <table class="tbl">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Jurnal</th>
                <th>Keterangan</th>
                <th>Akun</th>
                <th style="text-align:right;">Debit</th>
                <th style="text-align:right;">Kredit</th>
            </tr>
        </thead>
        <tbody>
            {{-- @foreach ($jurnal as $j)
      @php $first = true; @endphp
      @foreach ($j['entri'] as $e)
      <tr style="{{ $first ? 'border-top:2px solid var(--line-2);' : '' }}">
        <td style="color:var(--ink-3); white-space:nowrap; font-size:12.5px;">{{ $first ? $j['tanggal'] : '' }}</td>
        <td class="mono" style="font-size:11.5px; color:var(--ink-4);">{{ $first ? $j['noJurnal'] : '' }}</td>
        <td style="font-size:12.5px; color:var(--ink-3);">{{ $first ? $j['keterangan'] : '' }}</td>
        <td style="font-size:13px; {{ $e['posisi'] === 'kredit' ? 'padding-left:28px; color:var(--ink-3);' : 'font-weight:500;' }}">{{ $e['akun'] }}</td>
        <td class="num" style="text-align:right; font-size:13px;">{{ $e['posisi'] === 'debit' ? fmt_rp($e['jumlah']) : '' }}</td>
        <td class="num" style="text-align:right; font-size:13px;">{{ $e['posisi'] === 'kredit' ? fmt_rp($e['jumlah']) : '' }}</td>
      </tr>
      @php $first = false; @endphp
      @endforeach
      @endforeach --}}
            <template x-if="loading">
                <tr>
                    <td colspan="6" style="text-align:center; color:var(--ink-3); padding:20px;">
                        Memuat data...
                    </td>
                </tr>
            </template>
            <template x-if="!loading && Object.keys(tableData.data).length === 0">
                <tr>
                    <td colspan="6" style="text-align:center; color:var(--ink-3); padding:20px;">
                        Tidak ada data
                    </td>
                </tr>
            </template>
            <template x-if="!loading && Object.keys(tableData.data).length > 0">
                <template x-for="[groupName, items] in Object.entries(tableData.data)" :key="groupName">
                    <tbody>
                        <tr class="coa-group-row">
                            <td colspan="6" x-text="groupName"></td>
                        </tr>
                        <template x-for="item in items" :key="item.id">
                            <tr>
                                <td style="color:var(--ink-3); white-space:nowrap; font-size:12.5px;" x-text="item.tanggal"></td>
                                <td class="mono" style="font-size:11.5px; color:var(--ink-4);" x-text="item.noJurnal"></td>
                                <td style="font-size:12.5px; color:var(--ink-3);" x-text="item.keterangan"></td>
                                <td style="font-size:13px;" x-text="item.akun"></td>
                                <td class="num" style="text-align:right; font-size:13px;" x-text="item.posisi === 'debit' ? item.jumlah_formatted : ''"></td>
                                <td class="num" style="text-align:right; font-size:13px;" x-text="item.posisi === 'kredit' ? item.jumlah_formatted : ''"></td>
                            </tr>
                        </template>
                    </tbody>
                </template>
            </template>
        </tbody>
    </table>
</div>
@push('journal-scripts')
    <script>
        function journalModule() {
            return {
                tableData: {
                    data: {},
                    total: 0,
                },
                filter: {
                    search: '',
                    start_date: '{{ now()->startOfMonth()->format('Y-m-d') }}',
                    end_date: '{{ now()->endOfMonth()->format('Y-m-d') }}',
                },

                async init() {
                    // Any initialization logic can go here
                    this.loading = true;
                    await this.fetchData();
                    this.loading = false;
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('reports.journal.datatable'), {
                            params: {
                                search: this.filter.search,
                                start_date: this.filter.start_date,
                                end_date: this.filter.end_date
                            }
                        });
                        console.log('Journal data fetched:', r.data);
                        this.transformData(r.data);
                    } catch {
                        Toast.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan saat memuat data.'
                        });
                    } finally {
                        this.loading = false;
                    }
                },

                transformData(journalEntries) {
                    const groupedData = {};
                    let total = 0;

                    journalEntries.forEach(entry => {
                        if (!entry.items || entry.items.length === 0) return;

                        entry.items.forEach(item => {
                            // Group by account category name
                            const categoryName = item.account?.category?.name || 'Uncategorized';
                            
                            if (!groupedData[categoryName]) {
                                groupedData[categoryName] = [];
                            }

                            // Format currency for display
                            const jumlah_formatted = this.formatCurrency(item.debit || item.credit);
                            
                            groupedData[categoryName].push({
                                id: item.id,
                                tanggal: this.formatDate(entry.journal_date),
                                noJurnal: entry.number,
                                keterangan: entry.description,
                                akun: item.account?.name || 'Unknown',
                                posisi: item.debit > 0 ? 'debit' : 'kredit',
                                jumlah: item.debit || item.credit,
                                jumlah_formatted: jumlah_formatted
                            });

                            total += item.debit || item.credit;
                        });
                    });

                    this.tableData = {
                        data: groupedData,
                        total: total
                    };
                },

                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('id-ID', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                    });
                },

                formatCurrency(value) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(value);
                },
            }
        }
    </script>
@endpush
