@extends('layouts.auth')

@section('content')
<div class="auth-page">

    {{-- Left panel — branding --}}
    <div class="auth-brand">
        <div class="auth-brand__inner">
            <div class="auth-brand__logo">PPI</div>
            <div class="auth-brand__tagline display">Kelola bisnis<br>lebih cerdas.</div>
            <p class="auth-brand__sub">
                Platform ERP terintegrasi untuk penjualan, pembelian,<br>
                kas &amp; bank, dan laporan keuangan Anda.
            </p>
        </div>
        <div class="auth-brand__dots" aria-hidden="true">
            @for ($i = 0; $i < 35; $i++)
                <span></span>
            @endfor
        </div>
    </div>

    {{-- Right panel — form --}}
    <div class="auth-panel">
        <div class="auth-card">

            <div class="auth-card__head">
                <h1 class="auth-card__title display">Masuk</h1>
                <p class="auth-card__sub">Gunakan akun PPI Anda untuk melanjutkan.</p>
            </div>

            @if ($errors->any())
                <div class="auth-alert">
                    <x-misc.icon name="x" :size="15" sw="2" />
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="auth-form">
                @csrf

                <div class="auth-field">
                    <label for="email" class="label">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="input {{ $errors->has('email') ? 'input--error' : '' }}"
                        placeholder="nama@perusahaan.com"
                        autocomplete="email"
                        autofocus
                        required
                    />
                </div>

                <div class="auth-field">
                    <label for="password" class="label">Password</label>
                    <div class="auth-pass-wrap" x-data="{ show: false }">
                        <input
                            id="password"
                            :type="show ? 'text' : 'password'"
                            name="password"
                            class="input {{ $errors->has('password') ? 'input--error' : '' }}"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        />
                        <button type="button" class="auth-pass-toggle" @click="show = !show" tabindex="-1">
                            <x-misc.icon name="eye" :size="16" sw="1.6" />
                        </button>
                    </div>
                </div>

                <div class="auth-row">
                    <label class="auth-remember">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} />
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary auth-submit">
                    Masuk
                </button>
            </form>

        </div>

        <p class="auth-footer">
            &copy; {{ date('Y') }} Putra Pangan Indonesia. Seluruh hak dilindungi.
        </p>
    </div>

</div>
@endsection
