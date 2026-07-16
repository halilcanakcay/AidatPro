@extends('layouts.app', ['title' => 'Ödeme Al - AidatPro'])

@php
    $money = fn ($value) => '₺' . number_format((float) $value, 2, ',', '.');
    $selectedPaid = $selectedDue ? (float) $selectedDue->payments->sum('amount') : 0;
    $selectedPending = $selectedDue ? max(0, (float) $selectedDue->amount - $selectedPaid) : 0;
@endphp

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Tahsilat</div>
                    <h2 class="page-title">Ödeme Al</h2>
                    <div class="text-secondary mt-1">{{ $site->name }} içinde borcu olan daireyi arayın, seçin ve ödemeyi kaydedin.</div>
                </div>
                <div class="col-auto"><a class="btn" href="{{ route('dues.index') }}"><x-icon name="arrow-left" class=" me-1" /> Aidatlara Dön</a></div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-lg-8">
                    <div class="card mb-3">
                        <div class="card-body">
                            <form class="row g-2 align-items-end" method="get" action="{{ route('payments.create') }}">
                                <div class="col-md-3">
                                    <label class="form-label">Site</label>
                                    <select class="form-select" name="site_id">
                                        @foreach ($sites as $filterSite)
                                            <option value="{{ $filterSite->id }}" @selected($filterSite->is($site))>{{ $filterSite->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Blok</label>
                                    <select class="form-select" name="block_id">
                                        <option value="">Tüm bloklar</option>
                                        @foreach ($blocks as $block)
                                            <option value="{{ $block->id }}" @selected($blockId === $block->id)>{{ $block->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Daire / sakin / blok ara</label>
                                    <div class="input-icon">
                                        <span class="input-icon-addon"><x-icon name="search" /></span>
                                        <input class="form-control" name="search" value="{{ $search }}" placeholder="Örn: A-12, Elif Kaya, A Blok">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn w-100" type="submit"><x-icon name="filter" class="me-1" /> Filtrele</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <form class="card" method="post" action="{{ route('payments.store') }}">
                        @csrf
                        <div class="card-header"><h3 class="card-title">Ödeme Bilgileri</h3></div>
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger"><x-icon name="alert-circle" class=" me-2" />{{ $errors->first() }}</div>
                            @endif

                            @if ($dues->isEmpty())
                                <div class="empty">
                                    <div class="empty-icon"><x-icon name="circle-check" class=" fs-1" /></div>
                                    <p class="empty-title">Tahsil edilecek borç yok</p>
                                    <p class="empty-subtitle text-secondary">Yeni dönem için önce tahakkuk oluşturabilirsiniz.</p>
                                    <div class="empty-action"><a class="btn btn-primary" href="{{ route('dues.create') }}">Tahakkuk Oluştur</a></div>
                                </div>
                            @else
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Borç Seç</label>
                                        <select class="form-select" name="due_id" required>
                                            @foreach ($dues as $due)
                                                @php $pending = max(0, (float) $due->amount - (float) $due->payments->sum('amount')); @endphp
                                                <option value="{{ $due->id }}" @selected(old('due_id', $selectedDue?->id) == $due->id)>
                                                    {{ $due->apartment->buildingBlock->name }} / {{ $due->apartment->number }} - {{ $due->apartment->activeResident?->full_name ?? 'Sakin yok' }} - {{ $due->period_month }}/{{ $due->period_year }} - Kalan {{ $money($pending) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-hint">Arama alanı yalnızca borç listesini daraltır. Listede kalan kaydı seçip ödemeyi kaydedin.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tutar</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₺</span>
                                            <input class="form-control" type="number" name="amount" min="0.01" step="0.01" value="{{ old('amount', $selectedPending) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Yöntem</label>
                                        <select class="form-select" name="method">
                                            <option value="bank" @selected(old('method', 'bank') === 'bank')>Banka</option>
                                            <option value="eft" @selected(old('method') === 'eft')>EFT/Havale</option>
                                            <option value="card" @selected(old('method') === 'card')>Kart</option>
                                            <option value="cash" @selected(old('method') === 'cash')>Nakit</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Ödeme Tarihi</label>
                                        <input class="form-control" type="datetime-local" name="paid_at" value="{{ old('paid_at', now()->format('Y-m-d\\TH:i')) }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Makbuz No</label>
                                        <input class="form-control" name="receipt_no" value="{{ old('receipt_no') }}" placeholder="Boş bırakılırsa otomatik verilir">
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if ($dues->isNotEmpty())
                            <div class="card-footer text-end">
                                <button class="btn btn-primary" type="submit"><x-icon name="check" class=" me-1" /> Ödemeyi Kaydet</button>
                            </div>
                        @endif
                    </form>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Seçili Borç</h3></div>
                        <div class="list-group list-group-flush">
                            @if ($selectedDue)
                                <div class="list-group-item d-flex justify-content-between"><span>Daire</span><strong>{{ $selectedDue->apartment->number }}</strong></div>
                                <div class="list-group-item d-flex justify-content-between"><span>Sakin</span><strong>{{ $selectedDue->apartment->activeResident?->full_name ?? '-' }}</strong></div>
                                <div class="list-group-item d-flex justify-content-between"><span>Tahakkuk</span><strong>{{ $money($selectedDue->amount) }}</strong></div>
                                <div class="list-group-item d-flex justify-content-between"><span>Ödenen</span><strong>{{ $money($selectedPaid) }}</strong></div>
                                <div class="list-group-item d-flex justify-content-between"><span>Kalan</span><strong class="text-danger">{{ $money($selectedPending) }}</strong></div>
                            @else
                                <div class="list-group-item text-secondary">Seçili borç yok.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
