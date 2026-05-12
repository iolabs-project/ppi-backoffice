@extends('layouts.erp')
@section('content')
<div x-data="{
  modal: null,
  transfer: { dari: '', ke: '', jumlah: '', keterangan: '' },
  kirim: { akun: '', penerima: '', bank: '', norek: '', jumlah: '', keterangan: '' },
  terima: { akun: '', pengirim: '', jumlah: '', ref: '', keterangan: '' },
  tambah: { nama: '', bank: '', norek: '', saldo: '', keterangan: '' },
}" style="padding:24px 28px 60px; display:grid; gap:16px;">

  <div style="display:flex; justify-content:space-between; align-items:center; gap:16px;">
    <div>
      <h1 class="display" style="margin:0; font-size:26px; font-weight:700; letter-spacing:-0.02em;">Kas &amp; Bank</h1>
      <div style="font-size:13px; color:var(--ink-4); margin-top:4px;">{{ count($akunKas) }} rekening aktif · Total saldo</div>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
      <button class="btn btn-ghost" x-on:click="modal = 'transfer'">
        <x-erp.icon name="swap" :size="14" />Transfer Dana
      </button>
      <button class="btn btn-ghost" x-on:click="modal = 'kirim'">
        <x-erp.icon name="send" :size="14" />Kirim Dana
      </button>
      <button class="btn btn-ghost" x-on:click="modal = 'terima'">
        <x-erp.icon name="inbox" :size="14" />Terima Dana
      </button>
      <button class="btn btn-primary" x-on:click="modal = 'tambah'">
        <x-erp.icon name="plus" :size="14" />Tambah Rekening
      </button>
    </div>
  </div>

  {{-- Total saldo card --}}
  <div class="card" style="padding:22px 26px; display:flex; align-items:center; justify-content:space-between; background:var(--ink); color:var(--paper);">
    <div>
      <div style="font-size:12px; opacity:.6; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">Total Saldo Tersedia</div>
      <div class="display num" style="font-size:32px; font-weight:700; letter-spacing:-0.03em;">{{ fmt_rp($totalSaldo) }}</div>
      <div style="font-size:12px; opacity:.5; margin-top:4px;">{{ count($akunKas) }} rekening · diperbarui hari ini</div>
    </div>
    <div style="opacity:.15;">
      <x-erp.icon name="bank" :size="56" />
    </div>
  </div>

  {{-- Account cards --}}
  <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:14px;">
    @foreach($akunKas as $ak)
    @php
      $pct = $totalSaldo > 0 ? round($ak['saldo'] / $totalSaldo * 100) : 0;
      $isKas = str_contains(strtolower($ak['nama']), 'kas');
    @endphp
    <a href="{{ route('erp.kasbank.show', $ak['id']) }}" style="text-decoration:none; color:inherit;">
      <div class="card" style="padding:20px 22px; display:grid; gap:12px; cursor:pointer; transition:box-shadow .15s;"
           onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.08)'" onmouseout="this.style.boxShadow=''">
        <div style="display:flex; align-items:flex-start; justify-content:space-between;">
          <div style="display:flex; align-items:center; gap:10px;">
            <div style="width:38px; height:38px; border-radius:10px; background:var(--accent-3); display:grid; place-items:center;">
              <x-erp.icon :name="$isKas ? 'wallet' : 'bank'" :size="18" stroke="var(--accent)" />
            </div>
            <div>
              <div style="font-weight:700; font-size:14px; line-height:1.2;">{{ $ak['nama'] }}</div>
              <div style="font-size:11.5px; color:var(--ink-4); margin-top:2px;">{{ $ak['bank'] }}
                @if($ak['norek'])<span class="mono"> · {{ $ak['norek'] }}</span>@endif
              </div>
            </div>
          </div>
          <span style="font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.07em; color:var(--accent); background:var(--accent-3); padding:3px 8px; border-radius:999px;">
            {{ $pct }}%
          </span>
        </div>
        <div>
          <div class="num display" style="font-size:22px; font-weight:700; color:var(--ink);">{{ fmt_rp($ak['saldo']) }}</div>
          <div style="margin-top:8px; height:4px; border-radius:2px; background:var(--line);">
            <div style="height:4px; border-radius:2px; background:var(--accent); width:{{ $pct }}%;"></div>
          </div>
        </div>
        <div style="display:flex; justify-content:space-between; font-size:12px; color:var(--ink-4);">
          <span>{{ $ak['transaksi'] ?? 0 }} transaksi bulan ini</span>
          <span style="color:var(--ink-3);">Lihat detail →</span>
        </div>
      </div>
    </a>
    @endforeach
  </div>

  {{-- Recent transactions --}}
  <div class="card" style="overflow:hidden;">
    <div style="padding:14px 22px; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between;">
      <div class="display" style="font-weight:700; font-size:15px;">Transaksi Terbaru</div>
      <button class="btn btn-ghost btn-sm"><x-erp.icon name="download" :size="13" />Ekspor</button>
    </div>
    <table class="tbl">
      <thead><tr>
        <th>Tanggal</th><th>Keterangan</th><th>Akun</th><th>Ref</th>
        <th style="text-align:right;">Masuk</th><th style="text-align:right;">Keluar</th>
        <th style="text-align:right;">Saldo</th>
      </tr></thead>
      <tbody>
        @foreach($transaksiKas as $tx)
        <tr>
          <td style="color:var(--ink-3); white-space:nowrap;">{{ $tx['tanggal'] }}</td>
          <td style="font-weight:500;">{{ $tx['keterangan'] }}</td>
          <td style="font-size:12px; color:var(--ink-4);">{{ $tx['akun'] }}</td>
          <td class="mono" style="font-size:11.5px; color:var(--ink-4);">{{ $tx['ref'] ?? '—' }}</td>
          <td class="num" style="text-align:right; color:{{ $tx['masuk'] ? 'var(--good)' : 'var(--ink-5)' }}; font-weight:{{ $tx['masuk'] ? 600 : 400 }};">
            {{ $tx['masuk'] ? fmt_rp($tx['masuk']) : '—' }}
          </td>
          <td class="num" style="text-align:right; color:{{ $tx['keluar'] ? 'var(--bad)' : 'var(--ink-5)' }}; font-weight:{{ $tx['keluar'] ? 600 : 400 }};">
            {{ $tx['keluar'] ? fmt_rp($tx['keluar']) : '—' }}
          </td>
          <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($tx['saldo']) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Modal: Transfer Dana --}}
  <x-erp.modal title="Transfer Dana" show="modal === 'transfer'" close-handler="modal = null">
    <div style="display:grid; gap:14px;">
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <x-erp.field label="Dari Rekening" :required="true">
          <select class="input"><option>Pilih rekening...</option>
            @foreach($akunKas as $ak)<option>{{ $ak['nama'] }}</option>@endforeach
          </select>
        </x-erp.field>
        <x-erp.field label="Ke Rekening" :required="true">
          <select class="input"><option>Pilih rekening...</option>
            @foreach($akunKas as $ak)<option>{{ $ak['nama'] }}</option>@endforeach
          </select>
        </x-erp.field>
      </div>
      <x-erp.field label="Jumlah" :required="true">
        <input class="input num" style="text-align:right;" placeholder="0" />
      </x-erp.field>
      <x-erp.field label="Keterangan">
        <input class="input" placeholder="(opsional)" />
      </x-erp.field>
    </div>
    <x-slot:footer>
      <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
      <button class="btn btn-primary"><x-erp.icon name="check" :size="14" />Proses Transfer</button>
    </x-slot:footer>
  </x-erp.modal>

  {{-- Modal: Kirim Dana --}}
  <x-erp.modal title="Kirim Dana ke Pihak Luar" show="modal === 'kirim'" close-handler="modal = null">
    <div style="display:grid; gap:14px;">
      <x-erp.field label="Dari Rekening" :required="true">
        <select class="input"><option>Pilih rekening...</option>
          @foreach($akunKas as $ak)<option>{{ $ak['nama'] }}</option>@endforeach
        </select>
      </x-erp.field>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <x-erp.field label="Nama Penerima" :required="true">
          <input class="input" placeholder="Nama vendor / individu" />
        </x-erp.field>
        <x-erp.field label="Bank Penerima">
          <input class="input" placeholder="BCA, BRI, Mandiri..." />
        </x-erp.field>
      </div>
      <x-erp.field label="No. Rekening Tujuan" :required="true">
        <input class="input mono" placeholder="xxxx-xxxx-xxxx" />
      </x-erp.field>
      <x-erp.field label="Jumlah" :required="true">
        <input class="input num" style="text-align:right;" placeholder="0" />
      </x-erp.field>
      <x-erp.field label="Keterangan">
        <input class="input" placeholder="Pembayaran tagihan, dll." />
      </x-erp.field>
    </div>
    <x-slot:footer>
      <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
      <button class="btn btn-primary"><x-erp.icon name="send" :size="14" />Kirim Dana</button>
    </x-slot:footer>
  </x-erp.modal>

  {{-- Modal: Terima Dana --}}
  <x-erp.modal title="Terima Dana dari Pihak Luar" show="modal === 'terima'" close-handler="modal = null">
    <div style="display:grid; gap:14px;">
      <x-erp.field label="Ke Rekening" :required="true">
        <select class="input"><option>Pilih rekening...</option>
          @foreach($akunKas as $ak)<option>{{ $ak['nama'] }}</option>@endforeach
        </select>
      </x-erp.field>
      <x-erp.field label="Nama Pengirim" :required="true">
        <input class="input" placeholder="Nama klien / individu" />
      </x-erp.field>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <x-erp.field label="Jumlah" :required="true">
          <input class="input num" style="text-align:right;" placeholder="0" />
        </x-erp.field>
        <x-erp.field label="No. Referensi">
          <input class="input mono" placeholder="REF/INV..." />
        </x-erp.field>
      </div>
      <x-erp.field label="Keterangan">
        <input class="input" placeholder="(opsional)" />
      </x-erp.field>
    </div>
    <x-slot:footer>
      <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
      <button class="btn btn-primary"><x-erp.icon name="inbox" :size="14" />Catat Penerimaan</button>
    </x-slot:footer>
  </x-erp.modal>

  {{-- Modal: Tambah Rekening --}}
  <x-erp.modal title="Tambah Kas &amp; Bank" show="modal === 'tambah'" close-handler="modal = null">
    <div style="display:grid; gap:14px;">
      <x-erp.field label="Nama Rekening" :required="true">
        <input class="input" placeholder="cth. BCA Operasional" />
      </x-erp.field>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <x-erp.field label="Nama Bank">
          <input class="input" placeholder="BCA, BRI, Kas..." />
        </x-erp.field>
        <x-erp.field label="No. Rekening">
          <input class="input mono" placeholder="(opsional untuk kas)" />
        </x-erp.field>
      </div>
      <x-erp.field label="Saldo Awal">
        <input class="input num" style="text-align:right;" placeholder="0" />
      </x-erp.field>
      <x-erp.field label="Keterangan">
        <input class="input" placeholder="(opsional)" />
      </x-erp.field>
    </div>
    <x-slot:footer>
      <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
      <button class="btn btn-primary"><x-erp.icon name="check" :size="14" />Simpan Rekening</button>
    </x-slot:footer>
  </x-erp.modal>

</div>
@endsection
