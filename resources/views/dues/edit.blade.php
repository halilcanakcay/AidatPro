@extends('layouts.app', ['title' => 'Aidat Düzenle - AidatPro'])

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Aidat yönetimi</div>
                    <h2 class="page-title">{{ $due->apartment->number }} Aidat Kaydı</h2>
                    <div class="text-secondary mt-1">{{ $due->apartment->buildingBlock->name }} / {{ $due->apartment->activeResident?->full_name ?? 'Sakin yok' }}</div>
                </div>
                <div class="col-auto"><a class="btn" href="{{ route('dues.index', ['year' => $due->period_year, 'month' => $due->period_month]) }}"><x-icon name="arrow-left" class="me-1" /> Aidatlara Dön</a></div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form class="card" method="post" action="{{ route('dues.update', $due) }}">
                @csrf
                @method('put')
                <div class="card-header"><h3 class="card-title">Aidat Bilgileri</h3></div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger"><x-icon name="alert-circle" class="me-2" />{{ $errors->first() }}</div>
                    @endif
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Yıl</label>
                            <input class="form-control" type="number" name="period_year" value="{{ old('period_year', $due->period_year) }}" min="2020" max="2100" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ay</label>
                            <select class="form-select" name="period_month">
                                @foreach ($months as $number => $name)
                                    <option value="{{ $number }}" @selected(old('period_month', $due->period_month) == $number)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tutar</label>
                            <div class="input-group">
                                <span class="input-group-text">₺</span>
                                <input class="form-control" type="number" name="amount" value="{{ old('amount', $due->amount) }}" min="0.01" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Son Ödeme</label>
                            <input class="form-control" type="date" name="due_date" value="{{ old('due_date', $due->due_date->toDateString()) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Not</label>
                            <input class="form-control" name="note" value="{{ old('note', $due->note) }}">
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a class="btn" href="{{ route('dues.index', ['year' => $due->period_year, 'month' => $due->period_month]) }}">Vazgeç</a>
                    <button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> Güncelle</button>
                </div>
            </form>
        </div>
    </div>
@endsection
