@extends('layouts.app')
@section('content')
@php
    $total     = $subtotal;
    $isPaid    = $biaya['status'] === 'disetujui';
    $sisa      = $isPaid ? 0 : $total;
@endphp

<div class="order-page">

    {{-- Header --}}
    <div class="order-hd order-hd--start">
        <div>
            <a href="{{ route('biaya.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali ke Biaya
            </a>
            <div class="order-title-row">
                <h1 class="order-title display">{{ $biaya['nomor'] }}</h1>
                <x-misc.status-badge :status="$biaya['status']" />
            </div>
            <div class="order-sub">Dibuat {{ $biaya['tanggal'] }}</div>
        </div>
        <div class="order-actions">
            <button class="btn btn-ghost">
                <x-misc.icon name="print" :size="14" />Print
            </button>
            @if($biaya['status'] === 'menunggu')
                <button class="btn btn-ghost" style="color:var(--bad);">
                    <x-misc.icon name="x" :size="14" />Tolak
                </button>
                <button class="btn btn-primary">
                    <x-misc.icon name="check" :size="14" />Setujui
                </button>
            @endif
        </div>
    </div>

    {{-- Receipt Card --}}
    <div class="card">

        {{-- Penerima + Meta --}}
        <div class="biaya-receipt-hd">

            {{-- Penerima --}}
            <div>
                <div class="label" style="margin-bottom:8px;">Penerima</div>
                <div class="biaya-receipt-penerima">{{ $biaya['penerima']['nama'] }}</div>
                @if(!empty($biaya['penerima']['perusahaan']))
                    <div class="biaya-receipt-meta-row">
                        <x-misc.icon name="building" :size="13" stroke="var(--ink-4)" />
                        <span>{{ $biaya['penerima']['perusahaan'] }}</span>
                    </div>
                @endif
                @if(!empty($biaya['penerima']['telepon']))
                    <div class="biaya-receipt-meta-row">
                        <x-misc.icon name="send" :size="13" stroke="var(--ink-4)" />
                        <span>{{ $biaya['penerima']['telepon'] }}</span>
                    </div>
                @endif
                @if(!empty($biaya['penerima']['alamat']))
                    <div class="biaya-receipt-meta-row">
                        <x-misc.icon name="tag" :size="13" stroke="var(--ink-4)" />
                        <span>{{ $biaya['penerima']['alamat'] }}</span>
                    </div>
                @endif
            </div>

            {{-- Meta fields --}}
            <div class="biaya-receipt-fields">
                <div>
                    <div class="label">Tgl. Transaksi</div>
                    <div class="biaya-receipt-field-val">{{ $biaya['tanggal'] }}</div>
                </div>
                <div>
                    <div class="label">Nomor</div>
                    <div class="biaya-receipt-field-val mono">{{ $biaya['nomor'] }}</div>
                </div>
                <div>
                    <div class="label">Dibayar Dari</div>
                    <div class="biaya-receipt-field-val" style="display:flex; align-items:center; gap:6px;">
                        <x-misc.icon name="dollar" :size="13" stroke="var(--ink-4)" />
                        @if($biaya['bayarNanti'])
                            <span style="color:var(--ink-4); font-style:italic;">Bayar Nanti</span>
                        @else
                            {{ $biaya['dibayarDari']['nama'] }}
                            <span class="mono" style="font-size:11px; color:var(--ink-4);">({{ $biaya['dibayarDari']['id'] }})</span>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- Divider --}}
        <div style="height:1px; background:var(--line); margin:0 24px;"></div>

        {{-- Line items --}}
        <div class="biaya-receipt-items">
            @foreach($biaya['items'] as $item)
                <div class="biaya-receipt-item">
                    <div class="biaya-receipt-item__main">
                        <div class="biaya-receipt-item__desc">{{ $item['deskripsi'] }}</div>
                        <div class="biaya-receipt-item__akun">
                            {{ $item['akun_kode'] ? $item['akun_kode'] . ' – ' : '' }}{{ $item['akun_nama'] }}
                        </div>
                    </div>
                    <div class="biaya-receipt-item__amount num">{{ fmt_rp($item['jumlah']) }}</div>
                </div>
            @endforeach
        </div>

        {{-- Summary --}}
        <div class="biaya-receipt-summary">
            <div class="biaya-receipt-summary__row">
                <span class="biaya-receipt-summary__label">Sub Total</span>
                <span class="num">{{ fmt_rp($subtotal) }}</span>
            </div>
            <div style="height:1px; background:var(--line); margin:8px 0;"></div>
            <div class="biaya-receipt-summary__row biaya-receipt-summary__row--total">
                <span>Total</span>
                <span class="num display">{{ fmt_rp($total) }}</span>
            </div>
            @if($isPaid)
                <div class="biaya-receipt-summary__row" style="margin-top:4px;">
                    <span style="color:var(--accent); font-weight:500;">
                        Pembayaran {{ $biaya['dibayarDari']['nama'] }} ({{ $biaya['dibayarDari']['id'] }})
                    </span>
                    <span class="num" style="color:var(--accent); font-weight:600;">{{ fmt_rp($total) }}</span>
                </div>
            @endif
            <div style="height:1px; background:var(--line); margin:8px 0;"></div>
            <div class="biaya-receipt-summary__row biaya-receipt-summary__row--sisa">
                <span>Sisa Tagihan</span>
                <span class="num display">{{ fmt_rp($sisa) }}</span>
            </div>
        </div>

        {{-- Catatan --}}
        @if(!empty($biaya['catatan']))
            <div style="padding:16px 24px; border-top:1px solid var(--line); background:var(--bg-2); border-radius:0 0 var(--r-card) var(--r-card);">
                <div class="label" style="margin-bottom:6px;">Catatan</div>
                <div style="font-size:13px; color:var(--ink-3);">{{ $biaya['catatan'] }}</div>
            </div>
        @endif

    </div>

</div>
@endsection
