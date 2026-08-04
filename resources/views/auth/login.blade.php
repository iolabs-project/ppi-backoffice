@extends('layouts.auth')

@section('content')
    <div class="auth-page" x-data="loginForm()">

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
                        <label for="username" class="label">Username</label>
                        <input type="text" x-model="username" class="input" placeholder="username" />
                    </div>

                    <div class="auth-field">
                        <label for="password" class="label">Password</label>
                        <div class="auth-pass-wrap" x-data="{ showPassword: false }">
                            <input :type="showPassword ? 'text' : 'password'" x-model="password" class="input"
                                placeholder="••••••••" />
                            <button type="button" class="auth-pass-toggle" @click="showPassword = !showPassword"
                                tabindex="-1">
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

                    <button type="button" class="btn btn-primary auth-submit" @click="login" :disabled="loading">
                        <span x-show="!loading">Masuk</span>
                        <span x-show="loading">Memproses...</span>
                    </button>
                </form>

            </div>

            <p class="auth-footer">
                &copy; {{ date('Y') }} Putra Pangan Indonesia. Seluruh hak dilindungi.
            </p>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function loginForm() {
            return {
                username: '',
                password: '',
                loading: false,
                showPassword: false,

                async login() {
                    if (!this.username || !this.password) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Username dan password harus diisi'
                        });

                        return;
                    }

                    Swal.fire({
                        title: 'Memproses login...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const response = await axios.post(
                            route('login.post'), {
                                username: this.username,
                                password: this.password,
                            }
                        );

                        Swal.close();
                        Toast.fire({
                            icon: 'success',
                            title: 'Login berhasil'
                        });

                        window.location.href = response.data.redirect;

                    } catch (error) {
                        Swal.close();
                        let title = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';
                        let html = null;
                        if (error.response?.status === 422) {
                            title = 'Validasi gagal. Silakan periksa kembali input Anda.';
                            html = '<ul style="text-align:left; margin:0; padding-left:20px;">' +
                                Object.values(error.response.data.errors)
                                .flat()
                                .map(msg => `<li>${msg}</li>`)
                                .join('') +
                                '</ul>';
                        } else if (error.response?.data?.message) {
                            title = error.response.data.message;
                        }
                        Toast.fire({
                            icon: 'error',
                            title: title,
                            html: html
                        });

                    }
                }
            };
        }
    </script>
@endpush
