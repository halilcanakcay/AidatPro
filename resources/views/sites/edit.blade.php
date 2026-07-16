@extends('layouts.app', ['title' => 'Site Düzenle - AidatPro'])

@php
    $blockValues = old('blocks', $site->buildingBlocks->pluck('name')->push('')->all());
@endphp

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Site yönetimi</div>
                    <h2 class="page-title">{{ $site->name }} Sitesini Düzenle</h2>
                    <div class="text-secondary mt-1">Mevcut bloklar korunur, yazdığınız yeni bloklar eklenir.</div>
                </div>
                <div class="col-auto"><a class="btn" href="{{ route('sites.index') }}"><x-icon name="arrow-left" class="me-1" /> Listeye Dön</a></div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form class="card" method="post" action="{{ route('sites.update', $site) }}">
                @csrf
                @method('put')
                <div class="card-header"><h3 class="card-title">Site Bilgileri</h3></div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger"><x-icon name="alert-circle" class="me-2" />{{ $errors->first() }}</div>
                    @endif
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Site Adı</label>
                            <input class="form-control" name="name" value="{{ old('name', $site->name) }}" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Adres</label>
                            <input class="form-control" name="address" value="{{ old('address', $site->address) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Bloklar</label>
                            <div class="vstack gap-2" data-block-list>
                                @foreach ($blockValues as $blockValue)
                                    <div class="input-group">
                                        <span class="input-group-text"><x-icon name="building" /></span>
                                        <input class="form-control" name="blocks[]" value="{{ $blockValue }}" placeholder="Örn: A Blok">
                                    </div>
                                @endforeach
                            </div>
                            <button class="btn btn-sm mt-2" type="button" data-add-block>
                                <x-icon name="plus" class="me-1" /> Blok Satırı Ekle
                            </button>
                            <div class="form-hint">Her bloğu ayrı satıra yazın. Mevcut bloklar korunur, boş satırlar dikkate alınmaz.</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a class="btn" href="{{ route('sites.index') }}">Vazgeç</a>
                    <button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> Güncelle</button>
                </div>
            </form>
        </div>
    </div>
@endsection
