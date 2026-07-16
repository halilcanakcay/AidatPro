@extends('layouts.app', ['title' => 'Duyuru Düzenle - AidatPro'])

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Site iletişimi</div>
                    <h2 class="page-title">Duyuru Düzenle</h2>
                    <div class="text-secondary mt-1">{{ $announcement->title }}</div>
                </div>
                <div class="col-auto"><a class="btn" href="{{ route('announcements.index') }}"><x-icon name="arrow-left" class="me-1" /> Listeye Dön</a></div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form class="card" method="post" action="{{ route('announcements.update', $announcement) }}">
                @csrf
                @method('put')
                <div class="card-header"><h3 class="card-title">Duyuru Bilgileri</h3></div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger"><x-icon name="alert-circle" class="me-2" />{{ $errors->first() }}</div>
                    @endif
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Başlık</label>
                            <input class="form-control" name="title" value="{{ old('title', $announcement->title) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Yayın Tarihi</label>
                            <input class="form-control" type="date" name="publish_date" value="{{ old('publish_date', $announcement->publish_date->toDateString()) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">İçerik</label>
                            <textarea class="form-control" name="content" rows="5" required>{{ old('content', $announcement->content) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a class="btn" href="{{ route('announcements.index') }}">Vazgeç</a>
                    <button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> Güncelle</button>
                </div>
            </form>
        </div>
    </div>
@endsection
