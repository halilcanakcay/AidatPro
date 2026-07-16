@extends('layouts.app', ['title' => 'Giderler - AidatPro'])

@php
    $money = fn ($value) => '₺' . number_format((float) $value, 2, ',', '.');
@endphp

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Kasa yönetimi</div>
                    <h2 class="page-title">Giderler</h2>
                    <div class="text-secondary mt-1">Site kasasından çıkan bakım, personel ve hizmet giderleri.</div>
                </div>
                <div class="col-auto"><a class="btn btn-primary" href="{{ route('expenses.create') }}"><x-icon name="plus" class=" me-1" /> Gider Ekle</a></div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @if (session('status'))
                <div class="alert alert-success"><x-icon name="circle-check" class=" me-2" />{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger"><x-icon name="alert-circle" class="me-2" />{{ $errors->first() }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gider Listesi</h3>
                </div>
                <div class="card-body border-bottom">
                    <form class="row g-2 align-items-end" method="get" action="{{ route('expenses.index') }}">
                        <div class="col-md-3">
                            <label class="form-label">Site</label>
                            <select class="form-select" name="site_id">
                                @foreach ($sites as $filterSite)
                                    <option value="{{ $filterSite->id }}" @selected($site->id === $filterSite->id)>{{ $filterSite->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label">Yıl</label>
                            <input class="form-control" type="number" name="year" value="{{ $year }}" min="2020" max="2100">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label">Ay</label>
                            <select class="form-select" name="month">
                                <option value="">Tüm aylar</option>
                                @foreach ($months as $number => $name)
                                    <option value="{{ $number }}" @selected($month === $number)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="category">
                                <option value="">Tümü</option>
                                @foreach ($categories as $filterCategory)
                                    <option value="{{ $filterCategory }}" @selected($category === $filterCategory)>{{ $filterCategory }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Arama</label>
                            <input class="form-control" name="search" value="{{ $search }}" placeholder="Kategori veya açıklama">
                        </div>
                        <div class="col-auto">
                            <button class="btn" type="submit"><x-icon name="filter" class="me-1" /> Filtrele</button>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Kategori</th>
                                <th>Açıklama</th>
                                <th class="text-end">Tutar</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('d.m.Y') }}</td>
                                <td><span class="badge bg-blue-lt">{{ $expense->category }}</span></td>
                                <td>{{ $expense->description }}</td>
                                <td class="text-end text-danger fw-bold">{{ $money($expense->amount) }}</td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a class="btn btn-sm" href="{{ route('expenses.edit', $expense) }}">Düzenle</a>
                                        <form method="post" action="{{ route('expenses.destroy', $expense) }}" data-confirm="Gider silinsin mi?">
                                            @csrf
                                            @method('delete')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Sil</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary py-5">Gider kaydı bulunamadı.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $expenses->links() }}</div>
            </div>
        </div>
    </div>
@endsection
