<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Yazılımı İndir - AidatPro</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="page page-center login-bg">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <a class="d-inline-flex align-items-center gap-2 text-decoration-none text-dark" href="{{ route('downloads.show') }}">
                    <span class="brand-mark"><x-icon name="building-skyscraper" class="fs-2" /></span>
                    <span class="text-start">
                        <span class="d-block fw-bold fs-2">AidatPro</span>
                        <span class="d-block text-secondary small">Kaynak kodu paketi</span>
                    </span>
                </a>
            </div>

            <form class="card card-md" method="post" action="{{ route('downloads.download') }}" autocomplete="off">
                @csrf
                <div class="card-body">
                    <div class="mb-4">
                        <div class="badge bg-primary-lt mb-3">
                            <x-icon name="shield-lock" class="me-1" /> Korumalı indirme
                        </div>
                        <h1 class="h2 mb-1">İndirme şifresi</h1>
                        <p class="text-secondary mb-0">AidatPro kurulum paketini indirmek için erişim şifresini girin.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <x-icon name="alert-circle" class="me-2" />{{ $errors->first() }}
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="downloadPassword">Şifre</label>
                        <input
                            class="form-control @error('password') is-invalid @enderror"
                            id="downloadPassword"
                            type="password"
                            name="password"
                            inputmode="numeric"
                            autocomplete="current-password"
                            required
                            autofocus
                        >
                    </div>

                    <button class="btn btn-primary w-100" type="submit">
                        <x-icon name="download" class="me-1" /> Paketi İndir
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
