@extends('layouts.app', ['title' => 'Yetkili Ekle - AidatPro'])

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Erişim yönetimi</div>
                    <h2 class="page-title">Yetkili Ekle</h2>
                    <div class="text-secondary mt-1">Genel yönetici veya belirli bir siteye bağlı yönetici hesabı oluşturun.</div>
                </div>
                <div class="col-auto"><a class="btn" href="{{ route('authorities.index') }}"><x-icon name="arrow-left" class="me-1" /> Yetkililere Dön</a></div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form class="card form-card" method="post" action="{{ route('authorities.store') }}">
                @csrf
                @include('authorities.form', ['button' => 'Yetkili Oluştur'])
            </form>
        </div>
    </div>
@endsection
