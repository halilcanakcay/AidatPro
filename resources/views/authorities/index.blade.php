@extends('layouts.app', ['title' => 'Yetkililer - AidatPro'])

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Erişim yönetimi</div>
                    <h2 class="page-title">Yetkililer</h2>
                    <div class="text-secondary mt-1">Panel kullanıcılarını ve daire sahiplerine gönderilecek süreli rapor bağlantılarını yönetin.</div>
                </div>
                <div class="col-auto">
                    <a class="btn btn-primary" href="{{ route('authorities.create') }}"><x-icon name="user-cog" class="me-1" /> Yetkili Ekle</a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @if (session('status'))
                <div class="alert alert-success"><x-icon name="circle-check" class="me-2" />{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger"><x-icon name="alert-circle" class="me-2" />{{ $errors->first() }}</div>
            @endif

            <div class="card mb-3">
                <div class="card-body">
                    <form class="row g-2 align-items-end" method="get" action="{{ route('authorities.index') }}">
                        <div class="col-md-5 col-lg-4">
                            <label class="form-label">Site</label>
                            <select class="form-select" name="site_id" onchange="this.form.submit()">
                                @foreach ($sites as $filterSite)
                                    <option value="{{ $filterSite->id }}" @selected($site && $filterSite->is($site))>{{ $filterSite->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <label class="form-label">Blok</label>
                            <select class="form-select" name="block_id">
                                <option value="">Tüm bloklar</option>
                                @foreach ($blocks as $block)
                                    <option value="{{ $block->id }}" @selected($blockId === $block->id)>{{ $block->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button class="btn" type="submit"><x-icon name="filter" class="me-1" /> Uygula</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row row-cards">
                <div class="col-xl-7">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Panel Kullanıcıları</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Kullanıcı</th>
                                        <th>Yetki</th>
                                        <th>Site</th>
                                        <th>Durum</th>
                                        <th class="w-1"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($users as $user)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $user->name }}</div>
                                            <div class="text-secondary small">{{ $user->email }}</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $user->role === 'admin' ? 'bg-blue-lt' : 'bg-indigo-lt' }}">
                                                {{ $user->role === 'admin' ? 'Genel yönetici' : 'Site yöneticisi' }}
                                            </span>
                                        </td>
                                        <td>{{ $user->site?->name ?? 'Tüm siteler' }}</td>
                                        <td>
                                            <span class="badge {{ $user->is_active ? 'bg-green-lt' : 'bg-red-lt' }}">
                                                {{ $user->is_active ? 'Aktif' : 'Pasif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-list flex-nowrap">
                                                <a class="btn btn-sm" href="{{ route('authorities.edit', $user) }}"><x-icon name="edit" class="me-1" /> Düzenle</a>
                                                <form method="post" action="{{ route('authorities.destroy', $user) }}" data-confirm="Yetkili hesabı silinsin mi?">
                                                    @csrf
                                                    @method('delete')
                                                    <button class="btn btn-sm btn-outline-danger" type="submit"><x-icon name="trash" class="me-1" /> Sil</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-secondary py-5">Yetkili bulunamadı.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">{{ $users->links() }}</div>
                    </div>
                </div>

                <div class="col-xl-5">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Daire Sahibi Rapor Linki</h3>
                        </div>
                        <form method="post" action="{{ route('owner-report-links.store') }}">
                            @csrf
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Daire</label>
                                    <select class="form-select" name="apartment_id" required>
                                        @foreach ($apartments as $apartment)
                                            <option value="{{ $apartment->id }}">
                                                {{ $apartment->buildingBlock->site->name }} / {{ $apartment->buildingBlock->name }} / {{ $apartment->number }} - {{ $apartment->activeResident?->full_name ?? 'Sakin yok' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Geçerlilik</label>
                                    <div class="input-group">
                                        <input class="form-control" type="number" name="expires_days" min="1" max="365" value="30" required>
                                        <span class="input-group-text">gün</span>
                                    </div>
                                </div>
                                <div class="alert alert-info mb-0">
                                    <x-icon name="link" class="me-2" />
                                    Oluşturulan bağlantı sadece seçilen dairenin aylık ve yıllık raporunu gösterir.
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button class="btn btn-primary" type="submit"><x-icon name="key" class="me-1" /> Geçici Link Oluştur</button>
                            </div>
                        </form>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Aktif Rapor Bağlantıları</h3>
                        </div>
                        <div class="list-group list-group-flush">
                            @forelse ($ownerLinks as $link)
                                @php $url = route('owner.reports.show', $link->token); @endphp
                                <div class="list-group-item">
                                    <div class="d-flex align-items-start gap-3">
                                        <span class="avatar bg-primary-lt"><x-icon name="link" /></span>
                                        <div class="flex-fill min-w-0">
                                            <div class="fw-bold">
                                                {{ $link->apartment->buildingBlock->name }} / {{ $link->apartment->number }}
                                            </div>
                                            <div class="text-secondary small">
                                                {{ $link->apartment->activeResident?->full_name ?? 'Sakin yok' }} ·
                                                {{ $link->is_expired ? 'Süresi doldu' : $link->expires_at?->format('d.m.Y H:i') . ' tarihine kadar' }}
                                            </div>
                                            <div class="input-group input-group-sm mt-2">
                                                <input class="form-control" value="{{ $url }}" readonly>
                                                <button class="btn" type="button" onclick="navigator.clipboard.writeText('{{ $url }}')"><x-icon name="copy" /></button>
                                                <a class="btn" href="{{ $url }}" target="_blank" rel="noopener"><x-icon name="external-link" /></a>
                                            </div>
                                        </div>
                                        <form method="post" action="{{ route('owner-report-links.destroy', $link) }}" data-confirm="Rapor bağlantısı silinsin mi?">
                                            @csrf
                                            @method('delete')
                                            <button class="btn btn-icon btn-outline-danger" type="submit" aria-label="Sil"><x-icon name="trash" /></button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item text-secondary">Henüz rapor bağlantısı oluşturulmadı.</div>
                            @endforelse
                        </div>
                        <div class="card-footer">{{ $ownerLinks->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
