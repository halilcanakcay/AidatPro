@extends('layouts.app', ['title' => 'Daireler - AidatPro'])

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Site envanteri</div>
                    <h2 class="page-title">Daireler</h2>
                    <div class="text-secondary mt-1">{{ $site->name }} içindeki bağımsız bölümler ve aktif sakinler.</div>
                </div>
                <div class="col-auto">
                    <a class="btn btn-primary" href="{{ route('apartments.create') }}">
                        <x-icon name="plus" class=" me-1" /> Yeni Daire
                    </a>
                </div>
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

            <div class="row row-cards mb-3">
                <div class="col-sm-4">
                    <div class="card"><div class="card-body"><div class="text-secondary">Toplam Daire</div><div class="h1 mb-0">{{ (int) $stats->total }}</div></div></div>
                </div>
                <div class="col-sm-4">
                    <div class="card"><div class="card-body"><div class="text-secondary">Dolu</div><div class="h1 mb-0 text-success">{{ (int) $stats->occupied }}</div></div></div>
                </div>
                <div class="col-sm-4">
                    <div class="card"><div class="card-body"><div class="text-secondary">Boş</div><div class="h1 mb-0 text-secondary">{{ (int) $stats->empty_count }}</div></div></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daire Listesi</h3>
                    <div class="card-actions">
                        <form class="row g-2" method="get" action="{{ route('apartments.index') }}">
                            <div class="col-auto">
                                <select class="form-select" name="site_id" onchange="this.form.submit()">
                                    @foreach ($sites as $filterSite)
                                        <option value="{{ $filterSite->id }}" @selected($filterSite->is($site))>{{ $filterSite->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <select class="form-select" name="block_id">
                                    <option value="">Tüm bloklar</option>
                                    @foreach ($blocks as $block)
                                        <option value="{{ $block->id }}" @selected($blockId === $block->id)>{{ $block->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <input class="form-control" name="search" value="{{ $search }}" placeholder="Daire, blok veya sakin ara">
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
                                <th>Daire</th>
                                <th>Sakin</th>
                                <th>Kat</th>
                                <th>Durum</th>
                                <th>İletişim</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($apartments as $apartment)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $apartment->number }}</div>
                                    <div class="text-secondary small">{{ $apartment->buildingBlock->name }}</div>
                                </td>
                                <td>{{ $apartment->activeResident?->full_name ?? 'Sakin yok' }}</td>
                                <td>{{ $apartment->floor_no ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $apartment->status === 'occupied' ? 'bg-green-lt' : 'bg-secondary-lt' }}">
                                        {{ $apartment->status === 'occupied' ? 'Dolu' : 'Boş' }}
                                    </span>
                                </td>
                                <td>{{ $apartment->activeResident?->phone ?? $apartment->activeResident?->email ?? '-' }}</td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a class="btn btn-sm" href="{{ route('apartments.edit', $apartment) }}">Düzenle</a>
                                        <form method="post" action="{{ route('apartments.destroy', $apartment) }}" data-confirm="Daire silinsin mi?">
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
                <div class="card-footer">{{ $apartments->links() }}</div>
            </div>
        </div>
    </div>
@endsection
