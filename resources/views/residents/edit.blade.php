@extends('layouts.app', ['title' => 'Sakin Düzenle - AidatPro'])

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Kişi yönetimi</div>
                    <h2 class="page-title">{{ $resident->full_name }} Kaydını Düzenle</h2>
                    <div class="text-secondary mt-1">Daire, iletişim ve aktiflik bilgisini güncelleyin.</div>
                </div>
                <div class="col-auto"><a class="btn" href="{{ route('residents.index') }}"><x-icon name="arrow-left" class="me-1" /> Listeye Dön</a></div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form class="card" method="post" action="{{ route('residents.update', $resident) }}">
                @csrf
                @method('put')
                <div class="card-header"><h3 class="card-title">Sakin Bilgileri</h3></div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger"><x-icon name="alert-circle" class="me-2" />{{ $errors->first() }}</div>
                    @endif
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Daire</label>
                            <select class="form-select" name="apartment_id" required>
                                @foreach ($apartments as $apartment)
                                    <option value="{{ $apartment->id }}" @selected(old('apartment_id', $resident->apartment_id) == $apartment->id)>
                                        {{ $apartment->buildingBlock->name }} / {{ $apartment->number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ad Soyad</label>
                            <input class="form-control" name="full_name" value="{{ old('full_name', $resident->full_name) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tip</label>
                            <select class="form-select" name="resident_type">
                                <option value="owner" @selected(old('resident_type', $resident->resident_type) === 'owner')>Ev sahibi</option>
                                <option value="tenant" @selected(old('resident_type', $resident->resident_type) === 'tenant')>Kiracı</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Telefon</label>
                            <input class="form-control" name="phone" value="{{ old('phone', $resident->phone) }}">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">E-posta</label>
                            <input class="form-control" type="email" name="email" value="{{ old('email', $resident->email) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Aktif</label>
                            <label class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $resident->is_active))>
                                <span class="form-check-label">Evet</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a class="btn" href="{{ route('residents.index') }}">Vazgeç</a>
                    <button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> Güncelle</button>
                </div>
            </form>
        </div>
    </div>
@endsection
