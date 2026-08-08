<x-misc.modal title="Penyesuaian Stok" show="modal === 'penyesuaian'" close-handler="modal = null" :width="620">
    <div class="form-body">

        {{-- Tipe --}}
        <x-misc.field label="Tipe penyesuaian stok" :required="true">
            <div class="ps-tipe-group">
                <label class="ps-tipe-btn" :class="penyesuaian.tipe === 'perhitungan' ? 'ps-tipe-btn--active' : ''">
                    <input type="radio" x-model="penyesuaian.tipe" value="perhitungan" style="display:none;" />
                    <x-misc.icon name="box" :size="14" />
                    Perhitungan Stok
                </label>
                <label class="ps-tipe-btn" :class="penyesuaian.tipe === 'masuk_keluar' ? 'ps-tipe-btn--active' : ''">
                    <input type="radio" x-model="penyesuaian.tipe" value="masuk_keluar" style="display:none;" />
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
                <input class="input" type="date" x-model="penyesuaian.tanggal" value="{{ date('Y-m-d') }}" />
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
                    <input class="input num" type="number" style="text-align:right;" x-model="penyesuaian.qtyAktual"
                        x-show="penyesuaian.tipe === 'perhitungan'" placeholder="0" />
                    <div class="ps-qty-cell--readonly" x-show="penyesuaian.tipe === 'masuk_keluar'" x-cloak
                        x-text="{{ $stok }} + Number(penyesuaian.selisih)"></div>
                </div>

                {{-- Selisih: read-only computed (perhitungan) / editable (masuk_keluar) --}}
                <div class="ps-qty-cell">
                    <div class="ps-qty-cell--readonly" x-show="penyesuaian.tipe === 'perhitungan'"
                        x-text="Number(penyesuaian.qtyAktual) - {{ $stok }}"></div>
                    <input class="input num" type="number" style="text-align:right;" x-model="penyesuaian.selisih"
                        x-show="penyesuaian.tipe === 'masuk_keluar'" x-cloak placeholder="0" />
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
