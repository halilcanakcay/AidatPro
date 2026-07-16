@extends('layouts.app', ['title' => 'Sakinler - AidatPro'])

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Kişi yönetimi</div>
                    <h2 class="page-title">Sakinler</h2>
                    <div class="text-secondary mt-1">Dairelerdeki aktif ve geçmiş sakin kayıtları.</div>
                </div>
                <div class="col-auto"><a class="btn btn-primary" href="{{ route('residents.create') }}"><x-icon name="user-plus" class=" me-1" /> Yeni Sakin</a></div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @if (session('status'))
                <div class="alert alert-success"><x-icon name="circle-check" class=" me-2" />{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger"><x-icon name="alert-circle" class="me-2" />{{ $errors->first() }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sakin Listesi</h3>
                    <div class="card-actions">
                        <form class="row g-2" method="get" action="{{ route('residents.index') }}">
                            <div class="col-auto">
                                <select class="form-select" name="site_id" aria-label="Site seç">
                                    @foreach ($sites as $filterSite)
                                        <option value="{{ $filterSite->id }}" @selected($site->id === $filterSite->id)>{{ $filterSite->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <select class="form-select" name="block_id" aria-label="Blok seç">
                                    <option value="">Tüm bloklar</option>
                                    @foreach ($blocks as $block)
                                        <option value="{{ $block->id }}" @selected($blockId === $block->id)>{{ $block->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <input class="form-control" name="search" value="{{ $search }}" placeholder="Ad, telefon veya daire ara">
                            </div>
                            <div class="col-auto">
                                <button class="btn" type="submit"><x-icon name="search" class="" /></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Sakin</th>
                                <th>Daire</th>
                                <th>Tip</th>
                                <th>Durum</th>
                                <th>İletişim</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($residents as $resident)
                            <tr>
                                <td class="fw-semibold">{{ $resident->full_name }}</td>
                                <td>
                                    <div>{{ $resident->apartment->number }}</div>
                                    <div class="text-secondary small">{{ $resident->apartment->buildingBlock->name }}</div>
                                </td>
                                <td>{{ $resident->resident_type === 'tenant' ? 'Kiracı' : 'Ev sahibi' }}</td>
                                <td><span class="badge {{ $resident->is_active ? 'bg-green-lt' : 'bg-secondary-lt' }}">{{ $resident->is_active ? 'Aktif' : 'Pasif' }}</span></td>
                                <td>
                                    <div>{{ $resident->phone ?? '-' }}</div>
                                    <div class="text-secondary small">{{ $resident->email ?? '' }}</div>
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a class="btn btn-sm" href="{{ route('residents.edit', $resident) }}">Düzenle</a>
                                        <form method="post" action="{{ route('residents.destroy', $resident) }}" data-confirm="Sakin silinsin mi?">
                                            @csrf
                                            @method('delete')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Sil</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-secondary py-5">Kayıt bulunamadı.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $residents->links() }}</div>
            </div>
        </div>
    </div>
@endsection
