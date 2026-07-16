@extends('layouts.app', ['title' => 'Gider Düzenle - AidatPro'])

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Kasa yönetimi</div>
                    <h2 class="page-title">Gider Düzenle</h2>
                    <div class="text-secondary mt-1">{{ $expense->description }}</div>
                </div>
                <div class="col-auto"><a class="btn" href="{{ route('expenses.index') }}"><x-icon name="arrow-left" class="me-1" /> Listeye Dön</a></div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form class="card" method="post" action="{{ route('expenses.update', $expense) }}">
                @csrf
                @method('put')
                <div class="card-header"><h3 class="card-title">Gider Bilgileri</h3></div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger"><x-icon name="alert-circle" class="me-2" />{{ $errors->first() }}</div>
                    @endif
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="category">
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}" @selected(old('category', $expense->category) === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tutar</label>
                            <div class="input-group">
                                <span class="input-group-text">₺</span>
                                <input class="form-control" type="number" name="amount" min="0.01" step="0.01" value="{{ old('amount', $expense->amount) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tarih</label>
                            <input class="form-control" type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->toDateString()) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Açıklama</label>
                            <input class="form-control" name="description" value="{{ old('description', $expense->description) }}" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a class="btn" href="{{ route('expenses.index') }}">Vazgeç</a>
                    <button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> Güncelle</button>
                </div>
            </form>
        </div>
    </div>
@endsection
