@extends('layouts.app', ['title' => 'Dashboard - AidatPro'])

@php
    $money = fn ($value) => '₺' . number_format((float) $value, 2, ',', '.');
@endphp

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Bugünkü işlerin özeti</div>
                    <h2 class="page-title">{{ $site->name }}</h2>
                    <div class="text-secondary mt-1">{{ $periodName }} dönemi için tahakkuk, tahsilat, kasa ve takip durumu.</div>
                </div>
                <div class="col-auto">
                    <div class="btn-list">
                        <form class="d-flex gap-2" method="get" action="{{ route('dashboard') }}">
                            <select class="form-select" name="site_id" aria-label="Site seç">
                                @foreach ($sites as $filterSite)
                                    <option value="{{ $filterSite->id }}" @selected($site->id === $filterSite->id)>{{ $filterSite->name }}</option>
                                @endforeach
                            </select>
                            <select class="form-select" name="block_id" aria-label="Blok seç">
                                <option value="">Tüm bloklar</option>
                                @foreach ($blocks as $block)
                                    <option value="{{ $block->id }}" @selected($blockId === $block->id)>{{ $block->name }}</option>
                                @endforeach
                            </select>
                            <input class="form-control site-filter-year" type="number" name="year" value="{{ $periodYear }}" min="2020" max="2100">
                            <select class="form-select" name="month">
                                @foreach ($months as $number => $name)
                                    <option value="{{ $number }}" @selected($periodMonth === $number)>{{ $name }}</option>
                                @endforeach
                            </select>
                            <button class="btn" type="submit"><x-icon name="filter" /></button>
                        </form>
                        <a class="btn" href="{{ route('payments.create') }}"><x-icon name="wallet" class=" me-1" /> Ödeme Al</a>
                        <a class="btn btn-primary" href="{{ route('dues.create') }}"><x-icon name="plus" class=" me-1" /> Tahakkuk Oluştur</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards mb-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card metric-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary fw-semibold">Kasa Bakiyesi</span>
                                <span class="avatar bg-primary-lt"><x-icon name="building-bank" class="" /></span>
                            </div>
                            <div class="h1 mt-4 mb-1">{{ $money($cashBalance) }}</div>
                            <div class="text-secondary small">Toplam gelir - gider</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card metric-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary fw-semibold">Dönem Tahakkuku</span>
                                <span class="avatar bg-blue-lt"><x-icon name="receipt" class="" /></span>
                            </div>
                            <div class="h1 mt-4 mb-1">{{ $money($summary->total_due) }}</div>
                            <div class="text-secondary small">{{ (int) $summary->due_count }} daire borçlandırıldı</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card metric-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary fw-semibold">Tahsil Edilen</span>
                                <span class="avatar bg-green-lt"><x-icon name="circle-check" class="" /></span>
                            </div>
                            <div class="h1 mt-4 mb-1">{{ $money($summary->total_paid) }}</div>
                            <div class="text-secondary small">%{{ $collectionRate }} tahsilat oranı</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card metric-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary fw-semibold">Kalan Borç</span>
                                <span class="avatar bg-red-lt"><x-icon name="alert-triangle" class="" /></span>
                            </div>
                            <div class="h1 mt-4 mb-1 text-danger">{{ $money($summary->total_pending) }}</div>
                            <div class="text-secondary small">{{ (int) $summary->unpaid_count + (int) $summary->partial_count }} daire takipte</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-cards">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Daire Bazlı Aidat Durumu</h3>
                            <div class="card-actions"><a class="btn btn-sm" href="{{ route('dues.index', ['site_id' => $site->id, 'block_id' => $blockId, 'year' => $periodYear, 'month' => $periodMonth]) }}">Tümünü Gör</a></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Daire</th>
                                        <th>Sakin</th>
                                        <th>Tahakkuk</th>
                                        <th>Ödenen</th>
                                        <th>Kalan</th>
                                        <th>Durum</th>
                                        <th class="w-1"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($dues as $due)
                                    @php
                                        $paid = (float) $due->payments->sum('amount');
                                        $amount = (float) $due->amount;
                                        $pending = max(0, $amount - $paid);
                                        $isLate = $pending > 0 && $due->due_date->isPast();
                                        $statusClass = $pending <= 0 ? 'bg-green-lt' : ($paid > 0 ? 'bg-yellow-lt' : 'bg-red-lt');
                                        $statusLabel = $pending <= 0 ? 'Ödendi' : ($paid > 0 ? 'Kısmi' : ($isLate ? 'Gecikti' : 'Bekliyor'));
                                        $sourceLabel = $paid > 0 ? $due->paymentSourceLabel() : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $due->apartment->number }}</div>
                                            <div class="text-secondary small">{{ $due->apartment->buildingBlock->name }}</div>
                                        </td>
                                        <td>{{ $due->apartment->activeResident?->full_name ?? 'Sakin yok' }}</td>
                                        <td>{{ $money($amount) }}</td>
                                        <td>{{ $money($paid) }}</td>
                                        <td class="{{ $pending > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">{{ $money($pending) }}</td>
                                        <td>
                                            <div class="d-flex flex-column align-items-start gap-1">
                                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                                @if ($sourceLabel)
                                                    <span class="badge {{ $due->paymentSourceBadgeClass() }}">{{ $sourceLabel }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if ($pending > 0)
                                                <a class="btn btn-sm" href="{{ route('payments.create', ['due_id' => $due->id]) }}">Öde</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-secondary py-5">Bu dönem için aidat yok.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="row row-cards">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h3 class="card-title">Hızlı İşlemler</h3></div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a class="btn justify-content-start op-command" href="{{ route('payments.create') }}"><x-icon name="wallet" class=" me-2" />Ödeme al</a>
                                        <a class="btn justify-content-start op-command" href="{{ route('apartments.create') }}"><x-icon name="building-plus" class=" me-2" />Yeni daire ekle</a>
                                        <a class="btn justify-content-start op-command" href="{{ route('dues.create') }}"><x-icon name="receipt" class=" me-2" />Toplu tahakkuk oluştur</a>
                                        <a class="btn justify-content-start op-command" href="{{ route('expenses.create') }}"><x-icon name="report-money" class=" me-2" />Gider ekle</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="collection-ring dashboard-ring" style="--rate: {{ $collectionRate }}%;">
                                                <div class="collection-ring-inner">
                                                    <div><strong>%{{ $collectionRate }}</strong><span class="text-secondary fw-semibold">Tahsilat</span></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <h3 class="card-title mb-2">Dönem Kontrolü</h3>
                                            <div class="text-secondary">{{ (int) $apartmentStats->occupied }} dolu, {{ (int) $apartmentStats->empty_count }} boş daire var.</div>
                                            <div class="mt-3">
                                                <a class="btn btn-sm" href="{{ route('apartments.index') }}">Daireleri Kontrol Et</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h3 class="card-title">Öncelikli Takip</h3></div>
                                <div class="list-group list-group-flush">
                                    @forelse ($followItems as $item)
                                        <div class="list-group-item">
                                            <div class="row align-items-center">
                                                <div class="col-auto"><span class="avatar bg-red-lt"><x-icon name="alert-circle" class="" /></span></div>
                                                <div class="col">
                                                    <div class="fw-semibold">{{ $item['due']->apartment->number }} {{ $item['due']->apartment->activeResident?->full_name }}</div>
                                                    <div class="text-secondary small">{{ $item['due']->apartment->buildingBlock->name }}</div>
                                                </div>
                                                <div class="col-auto text-danger fw-bold">{{ $money($item['pending']) }}</div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="list-group-item text-secondary">Takip edilecek borç yok.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Son Tahsilatlar</h3></div>
                        <div class="list-group list-group-flush">
                            @forelse ($recentPayments as $payment)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between gap-3">
                                        <div>
                                            <div class="fw-semibold">{{ $payment->due->apartment->number }} - {{ $payment->due->apartment->activeResident?->full_name ?? 'Sakin yok' }}</div>
                                            <div class="text-secondary small">{{ $payment->receipt_no }} / {{ strtoupper($payment->method) }}</div>
                                            <span class="badge {{ $payment->source_badge_class }} mt-1">{{ $payment->source_label }}</span>
                                        </div>
                                        <div class="fw-bold">{{ $money($payment->amount) }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item text-secondary">Tahsilat yok.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Son Giderler</h3></div>
                        <div class="list-group list-group-flush">
                            @forelse ($expenses as $expense)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between gap-3">
                                        <div>
                                            <div class="fw-semibold">{{ $expense->category }}</div>
                                            <div class="text-secondary small">{{ $expense->description }}</div>
                                        </div>
                                        <div class="text-danger fw-bold">{{ $money($expense->amount) }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item text-secondary">Gider yok.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Duyurular ve Güvenlik</h3></div>
                        <div class="list-group list-group-flush">
                            @forelse ($announcements as $announcement)
                                <div class="list-group-item">
                                    <div class="fw-semibold">{{ $announcement->title }}</div>
                                    <div class="text-secondary small">{{ $announcement->content }}</div>
                                </div>
                            @empty
                                <div class="list-group-item text-secondary">Duyuru yok.</div>
                            @endforelse
                            @foreach ($auditLogs as $log)
                                <div class="list-group-item">
                                    <div class="fw-semibold">{{ $log->user?->name ?? 'Sistem' }}</div>
                                    <div class="text-secondary small">{{ $log->description ?? $log->action }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
