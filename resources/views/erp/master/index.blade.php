@extends('layouts.erp')
@section('content')
<div x-data="{
  tab: 'produk',
  modal: null,
  produkForm: { kode:'', nama:'', satuan:'', kategori:'', hargaBeli:0, hargaJual:0 },
  kontakForm: { nama:'', tipe:'klien', email:'', telepon:'', alamat:'', kota:'' },
  akunForm:   { kode:'', nama:'', tipe:'', saldo:0 },
  gudangForm: { kode:'', nama:'', kota:'', kapasitas:0, PIC:'' },
}" style="padding:24px 28px 60px; display:grid; gap:16px;">

  <div style="display:flex; justify-content:space-between; align-items:center; gap:16px;">
    <div>
      <h1 class="display" style="margin:0; font-size:26px; font-weight:700; letter-spacing:-0.02em;">Master Data</h1>
      <div style="font-size:13px; color:var(--ink-4); margin-top:4px;">Kelola data referensi untuk seluruh modul ERP</div>
    </div>
    <div>
      <button class="btn btn-primary" x-on:click="modal = 'add_' + tab">
        <x-erp.icon name="plus" :size="14" />
        <span x-text="{ produk:'Tambah Produk', kontak:'Tambah Kontak', akun:'Tambah Akun', gudang:'Tambah Gudang' }[tab]"></span>
      </button>
    </div>
  </div>

  {{-- Tab bar --}}
  <div class="utab">
    @foreach([['produk','Produk'],['kontak','Kontak'],['akun','Chart of Accounts'],['gudang','Gudang']] as [$id,$lbl])
    <button class="utab-item" x-on:click="tab = '{{ $id }}'" :class="tab === '{{ $id }}' ? 'utab-active' : ''">{{ $lbl }}</button>
    @endforeach
  </div>

  {{-- =================== PRODUK =================== --}}
  <div x-show="tab === 'produk'" x-cloak>
    <div class="card" style="overflow:hidden;">
      <div style="padding:10px 16px; border-bottom:1px solid var(--line); display:flex; align-items:center; gap:10px;">
        <div style="position:relative; flex:1; max-width:320px;">
          <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%);"><x-erp.icon name="search" :size="14" stroke="var(--ink-4)" /></span>
          <input class="input" style="padding-left:32px;" placeholder="Cari produk..." />
        </div>
        <button class="btn btn-ghost btn-sm"><x-erp.icon name="filter" :size="13" />Filter</button>
      </div>
      <table class="tbl">
        <thead><tr>
          <th style="width:36px;"><input type="checkbox" /></th>
          <th>Kode</th><th>Nama Produk</th><th>Kategori</th><th>Satuan</th>
          <th style="text-align:right;">Harga Beli</th><th style="text-align:right;">Harga Jual</th>
          <th style="text-align:right;">Stok</th><th style="width:40px;"></th>
        </tr></thead>
        <tbody>
          @foreach($produk as $p)
          <tr class="row-tap">
            <td x-on:click.stop><input type="checkbox" /></td>
            <td class="mono" style="font-weight:600; font-size:12px; color:var(--ink-4);">{{ $p['kode'] }}</td>
            <td>
              <div style="font-weight:600; font-size:13px;">{{ $p['nama'] }}</div>
              @if(!empty($p['deskripsi']))<div style="font-size:11px; color:var(--ink-4);">{{ $p['deskripsi'] }}</div>@endif
            </td>
            <td><span class="chip">{{ $p['kategori'] }}</span></td>
            <td style="color:var(--ink-3); font-size:13px;">{{ $p['satuan'] }}</td>
            <td class="num" style="text-align:right; font-size:13px;">{{ fmt_rp($p['hargaBeli']) }}</td>
            <td class="num" style="text-align:right; font-size:13px; font-weight:600;">{{ fmt_rp($p['hargaJual']) }}</td>
            <td class="num" style="text-align:right; font-size:13px;">{{ $p['stok'] ?? 0 }}</td>
            <td x-on:click.stop>
              <button class="btn btn-ghost btn-icon btn-sm" style="border:none;">
                <x-erp.icon name="more" :size="15" />
              </button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  {{-- =================== KONTAK =================== --}}
  <div x-show="tab === 'kontak'" x-cloak>
    <div class="card" style="overflow:hidden;">
      <div style="padding:10px 16px; border-bottom:1px solid var(--line); display:flex; align-items:center; gap:10px;">
        <div style="position:relative; flex:1; max-width:320px;">
          <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%);"><x-erp.icon name="search" :size="14" stroke="var(--ink-4)" /></span>
          <input class="input" style="padding-left:32px;" placeholder="Cari kontak..." />
        </div>
        <button class="btn btn-ghost btn-sm"><x-erp.icon name="filter" :size="13" />Filter</button>
      </div>
      <table class="tbl">
        <thead><tr>
          <th style="width:36px;"><input type="checkbox" /></th>
          <th>Nama</th><th>Tipe</th><th>Email</th><th>Telepon</th><th>Kota</th><th style="width:40px;"></th>
        </tr></thead>
        <tbody>
          @foreach($kontak as $k)
          <tr class="row-tap">
            <td x-on:click.stop><input type="checkbox" /></td>
            <td>
              <div style="display:flex; align-items:center; gap:10px;">
                <x-erp.avatar :name="$k['nama']" :size="32" />
                <span style="font-weight:600; font-size:13px;">{{ $k['nama'] }}</span>
              </div>
            </td>
            <td>
              <span class="chip" style="{{ strtolower($k['tipe']) === 'vendor' ? 'background:var(--accent-3); color:var(--accent);' : '' }}">
                {{ $k['tipe'] }}
              </span>
            </td>
            <td style="font-size:13px; color:var(--ink-3);">{{ $k['email'] ?? '—' }}</td>
            <td style="font-size:13px; color:var(--ink-3);">{{ $k['telepon'] ?? '—' }}</td>
            <td style="font-size:13px; color:var(--ink-3);">{{ $k['kota'] ?? '—' }}</td>
            <td x-on:click.stop>
              <button class="btn btn-ghost btn-icon btn-sm" style="border:none;">
                <x-erp.icon name="more" :size="15" />
              </button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  {{-- =================== AKUN / COA =================== --}}
  <div x-show="tab === 'akun'" x-cloak>
    <div class="card" style="overflow:hidden;">
      <div style="padding:10px 16px; border-bottom:1px solid var(--line); display:flex; align-items:center; gap:10px;">
        <div style="position:relative; flex:1; max-width:320px;">
          <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%);"><x-erp.icon name="search" :size="14" stroke="var(--ink-4)" /></span>
          <input class="input" style="padding-left:32px;" placeholder="Cari akun..." />
        </div>
      </div>
      <table class="tbl">
        <thead><tr>
          <th>Kode</th><th>Nama Akun</th><th>Tipe</th><th style="text-align:right;">Saldo Normal</th><th style="width:40px;"></th>
        </tr></thead>
        <tbody>
          @php
            $akunGroups = [];
            foreach($chartOfAccounts as $a) {
              $prefix = substr($a['kode'], 0, 1);
              $akunGroups[$prefix][] = $a;
            }
            $groupNames = ['1'=>'Aset','2'=>'Liabilitas','3'=>'Ekuitas','4'=>'Pendapatan','5'=>'Beban Pokok','6'=>'Beban Operasional'];
          @endphp
          @foreach($akunGroups as $prefix => $items)
          <tr style="background:var(--bg-2);">
            <td colspan="5" style="font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--ink-4); padding:8px 16px;">
              {{ $groupNames[$prefix] ?? 'Lainnya' }}
            </td>
          </tr>
          @foreach($items as $a)
          <tr class="row-tap">
            <td class="mono" style="font-weight:600; font-size:12px; color:var(--ink-4);">{{ $a['kode'] }}</td>
            <td style="font-weight:500; font-size:13px;">{{ $a['nama'] }}</td>
            <td><span class="chip">{{ $a['tipe'] ?? ucfirst($groupNames[$prefix] ?? '—') }}</span></td>
            <td class="num" style="text-align:right; font-weight:600; font-size:13px;">{{ fmt_rp($a['saldo']) }}</td>
            <td x-on:click.stop>
              <button class="btn btn-ghost btn-icon btn-sm" style="border:none;">
                <x-erp.icon name="more" :size="15" />
              </button>
            </td>
          </tr>
          @endforeach
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  {{-- =================== GUDANG =================== --}}
  <div x-show="tab === 'gudang'" x-cloak>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:14px;">
      @foreach($gudang as $g)
      <div class="card" style="padding:20px 22px; display:grid; gap:12px;">
        <div style="display:flex; align-items:flex-start; justify-content:space-between;">
          <div style="display:flex; align-items:center; gap:10px;">
            <div style="width:38px; height:38px; border-radius:10px; background:var(--accent-3); display:grid; place-items:center;">
              <x-erp.icon name="building" :size="18" stroke="var(--accent)" />
            </div>
            <div>
              <div style="font-weight:700; font-size:14px; line-height:1.2;">{{ $g['nama'] }}</div>
              <div class="mono" style="font-size:11px; color:var(--ink-4); margin-top:2px;">{{ $g['kode'] }}</div>
            </div>
          </div>
          <button class="btn btn-ghost btn-icon btn-sm" style="border:none;">
            <x-erp.icon name="more" :size="15" />
          </button>
        </div>
        <div style="display:grid; gap:6px; font-size:12.5px; color:var(--ink-3);">
          <div style="display:flex; align-items:center; gap:6px;">
            <x-erp.icon name="building" :size="12" stroke="var(--ink-4)" />{{ $g['kota'] ?? '—' }}
          </div>
          @if(!empty($g['PIC']))
          <div style="display:flex; align-items:center; gap:6px;">
            <x-erp.icon name="users" :size="12" stroke="var(--ink-4)" />PIC: {{ $g['PIC'] }}
          </div>
          @endif
          @if(!empty($g['kapasitas']))
          <div style="display:flex; align-items:center; gap:6px;">
            <x-erp.icon name="layers" :size="12" stroke="var(--ink-4)" />Kapasitas: {{ fmt_num($g['kapasitas']) }} unit
          </div>
          @endif
        </div>
      </div>
      @endforeach

      {{-- Add new gudang card --}}
      <div class="card" style="padding:20px 22px; display:grid; place-items:center; min-height:140px; border-style:dashed; cursor:pointer; opacity:.5; transition:opacity .15s;"
           onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='.5'"
           x-on:click="modal = 'add_gudang'">
        <div style="text-align:center;">
          <x-erp.icon name="plus" :size="24" stroke="var(--ink-3)" />
          <div style="font-size:13px; color:var(--ink-4); margin-top:8px;">Tambah Gudang</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal: Tambah Produk --}}
  <x-erp.modal title="Tambah Produk Baru" show="modal === 'add_produk'" close-handler="modal = null">
    <div style="display:grid; gap:14px;">
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <x-erp.field label="Kode Produk" :required="true">
          <input class="input mono" placeholder="cth. TPG-003" />
        </x-erp.field>
        <x-erp.field label="Kategori">
          <input class="input" placeholder="Tepung, Gula, Minyak..." />
        </x-erp.field>
      </div>
      <x-erp.field label="Nama Produk" :required="true">
        <input class="input" placeholder="Nama lengkap produk" />
      </x-erp.field>
      <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px;">
        <x-erp.field label="Satuan">
          <input class="input" placeholder="Sak, Kg, Liter..." />
        </x-erp.field>
        <x-erp.field label="Harga Beli">
          <input class="input num" style="text-align:right;" placeholder="0" />
        </x-erp.field>
        <x-erp.field label="Harga Jual">
          <input class="input num" style="text-align:right;" placeholder="0" />
        </x-erp.field>
      </div>
    </div>
    <x-slot:footer>
      <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
      <button class="btn btn-primary"><x-erp.icon name="check" :size="14" />Simpan Produk</button>
    </x-slot:footer>
  </x-erp.modal>

  {{-- Modal: Tambah Kontak --}}
  <x-erp.modal title="Tambah Kontak Baru" show="modal === 'add_kontak'" close-handler="modal = null">
    <div style="display:grid; gap:14px;">
      <x-erp.field label="Nama" :required="true">
        <input class="input" placeholder="Nama perusahaan / individu" />
      </x-erp.field>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <x-erp.field label="Tipe" :required="true">
          <select class="input">
            <option>Klien</option><option>Vendor</option><option>Keduanya</option>
          </select>
        </x-erp.field>
        <x-erp.field label="Kota">
          <input class="input" placeholder="Jakarta, Surabaya..." />
        </x-erp.field>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <x-erp.field label="Email">
          <input class="input" type="email" placeholder="kontak@perusahaan.com" />
        </x-erp.field>
        <x-erp.field label="Telepon">
          <input class="input" placeholder="08xx-xxxx-xxxx" />
        </x-erp.field>
      </div>
      <x-erp.field label="Alamat">
        <textarea class="input" rows="2" placeholder="Alamat lengkap..."></textarea>
      </x-erp.field>
    </div>
    <x-slot:footer>
      <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
      <button class="btn btn-primary"><x-erp.icon name="check" :size="14" />Simpan Kontak</button>
    </x-slot:footer>
  </x-erp.modal>

  {{-- Modal: Tambah Akun --}}
  <x-erp.modal title="Tambah Akun Baru" show="modal === 'add_akun'" close-handler="modal = null">
    <div style="display:grid; gap:14px;">
      <div style="display:grid; grid-template-columns:1fr 2fr; gap:14px;">
        <x-erp.field label="Kode Akun" :required="true">
          <input class="input mono" placeholder="1-xxx" />
        </x-erp.field>
        <x-erp.field label="Nama Akun" :required="true">
          <input class="input" placeholder="Nama akun" />
        </x-erp.field>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <x-erp.field label="Tipe Akun">
          <select class="input">
            <option>Aset</option><option>Liabilitas</option><option>Ekuitas</option>
            <option>Pendapatan</option><option>Beban</option>
          </select>
        </x-erp.field>
        <x-erp.field label="Saldo Awal">
          <input class="input num" style="text-align:right;" placeholder="0" />
        </x-erp.field>
      </div>
    </div>
    <x-slot:footer>
      <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
      <button class="btn btn-primary"><x-erp.icon name="check" :size="14" />Simpan Akun</button>
    </x-slot:footer>
  </x-erp.modal>

  {{-- Modal: Tambah Gudang --}}
  <x-erp.modal title="Tambah Gudang Baru" show="modal === 'add_gudang'" close-handler="modal = null">
    <div style="display:grid; gap:14px;">
      <div style="display:grid; grid-template-columns:1fr 2fr; gap:14px;">
        <x-erp.field label="Kode Gudang" :required="true">
          <input class="input mono" placeholder="GDG-xxx" />
        </x-erp.field>
        <x-erp.field label="Nama Gudang" :required="true">
          <input class="input" placeholder="Gudang Bekasi, dll." />
        </x-erp.field>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <x-erp.field label="Kota / Lokasi">
          <input class="input" placeholder="Bekasi, Surabaya..." />
        </x-erp.field>
        <x-erp.field label="Kapasitas (unit)">
          <input class="input num" style="text-align:right;" placeholder="0" />
        </x-erp.field>
      </div>
      <x-erp.field label="PIC (Penanggung Jawab)">
        <input class="input" placeholder="Nama PIC" />
      </x-erp.field>
    </div>
    <x-slot:footer>
      <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
      <button class="btn btn-primary"><x-erp.icon name="check" :size="14" />Simpan Gudang</button>
    </x-slot:footer>
  </x-erp.modal>

</div>
@endsection
