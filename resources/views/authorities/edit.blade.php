@extends('layouts.app', ['title' => 'Yetkili Düzenle - AidatPro'])

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Erişim yönetimi</div>
                    <h2 class="page-title">Yetkili Düzenle</h2>
                    <div class="text-secondary mt-1">{{ $user->name }} hesabının rolünü, sitesini ve durumunu güncelleyin.</div>
                </div>
                <div class="col-auto"><a class="btn" href="{{ route('authorities.index') }}"><x-icon name="arrow-left" class="me-1" /> Yetkililere Dön</a></div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form class="card form-card" method="post" action="{{ route('authorities.update', $user) }}">
                @csrf
                @method('put')
                @include('authorities.form', ['button' => 'Değişiklikleri Kaydet'])
            </form>
        </div>
    </div>
@endsection
