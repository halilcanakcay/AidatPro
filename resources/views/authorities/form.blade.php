<div class="card-header">
    <h3 class="card-title">Hesap Bilgileri</h3>
</div>
<div class="card-body">
    @if ($errors->any())
        <div class="alert alert-danger"><x-icon name="alert-circle" class="me-2" />{{ $errors->first() }}</div>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Ad Soyad</label>
            <input class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">E-posta</label>
            <input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Rol</label>
            <select class="form-select" name="role">
                <option value="site_manager" @selected(old('role', $user->role) === 'site_manager')>Site yöneticisi</option>
                <option value="admin" @selected(old('role', $user->role) === 'admin')>Genel yönetici</option>
            </select>
            <div class="form-hint">Genel yönetici tüm siteleri görebilir. Site yöneticisi seçilen site için çalışır.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Site</label>
            <select class="form-select" name="site_id">
                <option value="">Tüm siteler / genel yönetici</option>
                @foreach ($sites as $site)
                    <option value="{{ $site->id }}" @selected((string) old('site_id', $user->site_id) === (string) $site->id)>{{ $site->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Şifre</label>
            <input class="form-control" type="password" name="password" @if (! $user->exists) required @endif>
            @if ($user->exists)
                <div class="form-hint">Değiştirmek istemiyorsanız boş bırakın.</div>
            @endif
        </div>
        <div class="col-md-6">
            <label class="form-label">Şifre Tekrar</label>
            <input class="form-control" type="password" name="password_confirmation" @if (! $user->exists) required @endif>
        </div>
        <div class="col-12">
            <label class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))>
                <span class="form-check-label">Hesap aktif</span>
            </label>
        </div>
    </div>
</div>
<div class="card-footer text-end">
    <a class="btn" href="{{ route('authorities.index') }}">Vazgeç</a>
    <button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> {{ $button }}</button>
</div>
