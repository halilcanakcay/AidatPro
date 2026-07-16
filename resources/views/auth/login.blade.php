<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giriş - AidatPro</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="page page-center login-bg">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <a class="d-inline-flex align-items-center gap-2 text-decoration-none text-dark" href="{{ route('login') }}">
                    <span class="brand-mark"><x-icon name="building-skyscraper" class=" fs-2" /></span>
                    <span class="text-start">
                        <span class="d-block fw-bold fs-2">AidatPro</span>
                        <span class="d-block text-secondary small">Yönetim paneli</span>
                    </span>
                </a>
            </div>

            <form class="card card-md" method="post" action="{{ route('login.store') }}" autocomplete="off">
                @csrf

                <div class="card-body">
                    <div class="mb-4">
                        <div class="badge bg-primary-lt mb-3">
                            <x-icon name="shield-check" class=" me-1" /> Güvenli erişim
                        </div>
                        <h2 class="h2 mb-1">Yönetici girişi</h2>
                        <p class="text-secondary mb-0">Site finans operasyonunu yönetmek için hesabınızla oturum açın.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <div class="d-flex">
                                <div><x-icon name="alert-circle" class=" icon alert-icon" /></div>
                                <div>{{ $errors->first() }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">E-posta</label>
                        <input class="form-control" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Şifre</label>
                        <input class="form-control" type="password" name="password" autocomplete="current-password" required>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">
                        <x-icon name="login" class=" me-1" /> Giriş Yap
                    </button>

                    <div class="text-secondary small mt-3 text-center">Yetkili hesabınızla güvenli oturum açın.</div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
