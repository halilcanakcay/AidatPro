@extends('layouts.app', ['title' => 'Yeni Daire - AidatPro'])

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Daire yönetimi</div>
                    <h2 class="page-title">Yeni Daire Ekle</h2>
                    <div class="text-secondary mt-1">Daireyi ve isterseniz ilk sakini tek ekrandan kaydedin.</div>
                </div>
                <div class="col-auto"><a class="btn" href="{{ route('apartments.index') }}"><x-icon name="arrow-left" class=" me-1" /> Listeye Dön</a></div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form class="card" method="post" action="{{ route('apartments.store') }}">
                @csrf
                <div class="card-header"><h3 class="card-title">Daire Bilgileri</h3></div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger"><x-icon name="alert-circle" class=" me-2" />{{ $errors->first() }}</div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Blok</label>
                            <select class="form-select" name="building_block_id" required>
                                @foreach ($blocks as $block)
                                    <option value="{{ $block->id }}" @selected(old('building_block_id') == $block->id)>{{ $block->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Daire No</label>
                            <input class="form-control" name="number" value="{{ old('number') }}" placeholder="Örn: A-24" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Kat</label>
                            <input class="form-control" type="number" name="floor_no" value="{{ old('floor_no') }}" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Durum</label>
                            <select class="form-select" name="status">
                                <option value="occupied" @selected(old('status', 'occupied') === 'occupied')>Dolu</option>
                                <option value="empty" @selected(old('status') === 'empty')>Boş</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Sakin Adı</label>
                            <input class="form-control" name="resident_full_name" value="{{ old('resident_full_name') }}" placeholder="Boş bırakılabilir">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Telefon</label>
                            <input class="form-control" name="resident_phone" value="{{ old('resident_phone') }}" placeholder="05xx">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">E-posta</label>
                            <input class="form-control" type="email" name="resident_email" value="{{ old('resident_email') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tip</label>
                            <select class="form-select" name="resident_type">
                                <option value="owner" @selected(old('resident_type', 'owner') === 'owner')>Ev sahibi</option>
                                <option value="tenant" @selected(old('resident_type') === 'tenant')>Kiracı</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a class="btn" href="{{ route('apartments.index') }}">Vazgeç</a>
                    <button class="btn btn-primary" type="submit"><x-icon name="check" class=" me-1" /> Kaydet</button>
                </div>
            </form>
        </div>
    </div>
@endsection
