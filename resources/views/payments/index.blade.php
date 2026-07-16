@extends('layouts.app', ['title' => 'Ödemeler - AidatPro'])

@php
    $money = fn ($value) => '₺' . number_format((float) $value, 2, ',', '.');
@endphp

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Tahsilat yönetimi</div>
                    <h2 class="page-title">Ödemeler</h2>
                    <div class="text-secondary mt-1">Alınan ödemeler, makbuzlar ve tahsilat işlemleri.</div>
                </div>
                <div class="col-auto"><a class="btn btn-primary" href="{{ route('payments.create') }}"><x-icon name="plus" class="me-1" /> Ödeme Al</a></div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @if (session('status'))
                <div class="alert alert-success"><x-icon name="circle-check" class="me-2" />{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger"><x-icon name="alert-circle" class="me-2" />{{ $errors->first() }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ödeme Listesi</h3>
                </div>
                <div class="card-body border-bottom">
                    <form class="row g-2 align-items-end" method="get" action="{{ route('payments.index') }}">
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label">Site</label>
                            <select class="form-select" name="site_id" aria-label="Site seç">
                                @foreach ($sites as $filterSite)
                                    <option value="{{ $filterSite->id }}" @selected($site->id === $filterSite->id)>{{ $filterSite->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label">Blok</label>
                            <select class="form-select" name="block_id" aria-label="Blok seç">
                                <option value="">Tüm bloklar</option>
                                @foreach ($blocks as $block)
                                    <option value="{{ $block->id }}" @selected($blockId === $block->id)>{{ $block->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2 col-lg-1">
                            <label class="form-label">Yıl</label>
                            <input class="form-control" type="number" name="year" value="{{ $periodYear }}" min="2020" max="2100">
                        </div>
                        <div class="col-6 col-md-2 col-lg-2">
                            <label class="form-label">Ay</label>
                            <select class="form-select" name="month">
                                <option value="">Tüm aylar</option>
                                @foreach ($months as $number => $name)
                                    <option value="{{ $number }}" @selected($periodMonth === $number)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Yöntem</label>
                            <select class="form-select" name="method">
                                <option value="">Tümü</option>
                                <option value="bank" @selected($method === 'bank')>Banka</option>
                                <option value="eft" @selected($method === 'eft')>EFT</option>
                                <option value="card" @selected($method === 'card')>Kart</option>
                                <option value="cash" @selected($method === 'cash')>Nakit</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label">İşlem Kaynağı</label>
                            <select class="form-select" name="source">
                                <option value="">Tümü</option>
                                <option value="auto" @selected($source === 'auto')>Otomatik entegrasyon</option>
                                <option value="integration_manual" @selected($source === 'integration_manual')>Manuel entegrasyon</option>
                                <option value="panel" @selected($source === 'panel')>Panelden işlendi</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-lg-2">
                            <label class="form-label">Arama</label>
                            <input class="form-control" name="search" value="{{ $search }}" placeholder="Makbuz, daire veya sakin">
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
                                <th>Makbuz</th>
                                <th>Daire</th>
                                <th>Sakin</th>
                                <th>Yöntem</th>
                                <th>Kaynak</th>
                                <th>Tarih</th>
                                <th class="text-end">Tutar</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="fw-semibold">{{ $payment->receipt_no }}</td>
                                <td>{{ $payment->due->apartment->buildingBlock->name }} / {{ $payment->due->apartment->number }}</td>
                                <td>{{ $payment->due->apartment->activeResident?->full_name ?? '-' }}</td>
                                <td>{{ strtoupper($payment->method) }}</td>
                                <td><span class="badge {{ $payment->source_badge_class }}">{{ $payment->source_label }}</span></td>
                                <td>{{ $payment->paid_at->format('d.m.Y H:i') }}</td>
                                <td class="text-end fw-bold">{{ $money($payment->amount) }}</td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a class="btn btn-sm" href="{{ route('payments.receipt', $payment) }}">Makbuz</a>
                                        <a class="btn btn-sm" href="{{ route('payments.edit', $payment) }}">Düzenle</a>
                                        <form method="post" action="{{ route('payments.destroy', $payment) }}" data-confirm="Ödeme silinsin mi?">
                                            @csrf
                                            @method('delete')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Sil</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-secondary py-5">Ödeme kaydı yok.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $payments->links() }}</div>
            </div>
        </div>
    </div>
@endsection
