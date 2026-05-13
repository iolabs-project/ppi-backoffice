@extends('layouts.app')
@section('content')
<div class="coming-soon">
  <div class="coming-soon__inner">
    <div class="coming-soon__icon">
      <x-misc.icon name="layers" :size="32" stroke="var(--accent)" />
    </div>
    <h2 class="display coming-soon__title">
      {{ $title ?? 'Segera Hadir' }}
    </h2>
    <p class="coming-soon__desc">
      {{ $description ?? 'Modul ini sedang dalam pengembangan dan akan segera tersedia.' }}
    </p>
    <a href="{{ route('dashboard') }}" class="btn btn-ghost">
      <x-misc.icon name="chev-left" :size="13" />Kembali ke Dashboard
    </a>
  </div>
</div>
@endsection
