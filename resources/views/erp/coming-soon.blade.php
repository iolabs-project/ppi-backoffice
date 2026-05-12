@extends('layouts.erp')
@section('content')
<div style="padding:24px 28px 60px; display:grid; place-items:center; min-height:60vh;">
  <div style="text-align:center; max-width:400px;">
    <div style="width:72px; height:72px; border-radius:20px; background:var(--accent-3); display:grid; place-items:center; margin:0 auto 20px;">
      <x-erp.icon name="layers" :size="32" stroke="var(--accent)" />
    </div>
    <h2 class="display" style="font-size:22px; font-weight:700; margin:0 0 10px; letter-spacing:-0.02em;">
      {{ $title ?? 'Segera Hadir' }}
    </h2>
    <p style="font-size:14px; color:var(--ink-4); line-height:1.6; margin:0 0 24px;">
      {{ $description ?? 'Modul ini sedang dalam pengembangan dan akan segera tersedia.' }}
    </p>
    <a href="{{ route('erp.dashboard') }}" class="btn btn-ghost">
      <x-erp.icon name="chev-left" :size="13" />Kembali ke Dashboard
    </a>
  </div>
</div>
@endsection
