@extends('layouts.app', ['title' => 'Daire Düzenle - AidatPro'])

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Daire yönetimi</div>
                    <h2 class="page-title">{{ $apartment->number }} Dairesini Düzenle</h2>
                    <div class="text-secondary mt-1">Blok, daire no, kat ve doluluk bilgisini güncelleyin.</div>
                </div>
                <div class="col-auto"><a class="btn" href="{{ route('apartments.index') }}"><x-icon name="arrow-left" class="me-1" /> Listeye Dön</a></div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form class="card" method="post" action="{{ route('apartments.update', $apartment) }}">
                @csrf
                @method('put')
                <div class="card-header"><h3 class="card-title">Daire Bilgileri</h3></div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger"><x-icon name="alert-circle" class="me-2" />{{ $errors->first() }}</div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Blok</label>
                            <select class="form-select" name="building_block_id" required>
                                @foreach ($blocks as $block)
                                    <option value="{{ $block->id }}" @selected(old('building_block_id', $apartment->building_block_id) == $block->id)>{{ $block->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Daire No</label>
                            <input class="form-control" name="number" value="{{ old('number', $apartment->number) }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Kat</label>
                            <input class="form-control" type="number" name="floor_no" value="{{ old('floor_no', $apartment->floor_no) }}" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Durum</label>
                            <select class="form-select" name="status">
                                <option value="occupied" @selected(old('status', $apartment->status) === 'occupied')>Dolu</option>
                                <option value="empty" @selected(old('status', $apartment->status) === 'empty')>Boş</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a class="btn" href="{{ route('apartments.index') }}">Vazgeç</a>
                    <button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> Güncelle</button>
                </div>
            </form>
        </div>
    </div>
@endsection
