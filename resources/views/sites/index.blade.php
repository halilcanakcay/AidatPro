@extends('layouts.app', ['title' => 'Site Yönetimi - AidatPro'])

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Yapılandırma</div>
                    <h2 class="page-title">Site Yönetimi</h2>
                    <div class="text-secondary mt-1">Siteleri, adreslerini ve blok yapılarını yönetin.</div>
                </div>
                <div class="col-auto">
                    <a class="btn btn-primary" href="{{ route('sites.create') }}"><x-icon name="plus" class="me-1" /> Yeni Site</a>
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

            <div class="card">
                <div class="card-header"><h3 class="card-title">Site Listesi</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Site</th>
                                <th>Adres</th>
                                <th>Bloklar</th>
                                <th>Özet</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($sites as $site)
                            <tr>
                                <td class="fw-bold">{{ $site->name }}</td>
                                <td>{{ $site->address ?? '-' }}</td>
                                <td>
                                    @forelse ($site->buildingBlocks as $block)
                                        <span class="badge bg-blue-lt me-1">{{ $block->name }}</span>
                                    @empty
                                        <span class="text-secondary">Blok yok</span>
                                    @endforelse
                                </td>
                                <td>
                                    <span class="text-secondary">{{ $site->building_blocks_count }} blok</span>
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a class="btn btn-sm" href="{{ route('sites.edit', $site) }}">Düzenle</a>
                                        <form method="post" action="{{ route('sites.destroy', $site) }}" data-confirm="Site silinsin mi?">
                                            @csrf
                                            @method('delete')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Sil</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary py-5">Site kaydı yok.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $sites->links() }}</div>
            </div>
        </div>
    </div>
@endsection
