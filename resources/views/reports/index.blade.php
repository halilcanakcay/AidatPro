@extends('layouts.app', ['title' => 'Raporlar - AidatPro'])

@php
    $money = fn ($value) => '₺' . number_format((float) $value, 2, ',', '.');
@endphp

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Finansal analiz</div>
                    <h2 class="page-title">Raporlar</h2>
                    <div class="text-secondary mt-1">{{ $site->name }} için {{ $periodLabel }} tahakkuk, tahsilat, gider ve bakiye raporu.</div>
                </div>
                <div class="col-auto">
                    <a class="btn btn-primary" href="{{ route('reports.pdf', ['site_id' => $site->id, 'year' => $year, 'month' => $month]) }}">
                        <x-icon name="file-type-pdf" class="me-1" /> PDF İndir
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card mb-3">
                <div class="card-body">
                    <form class="row g-2 align-items-end" method="get" action="{{ route('reports.index') }}">
                        <div class="col-md-4">
                            <label class="form-label">Site</label>
                            <select class="form-select" name="site_id">
                                @foreach ($sites as $filterSite)
                                    <option value="{{ $filterSite->id }}" @selected($filterSite->is($site))>{{ $filterSite->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Yıl</label>
                            <input class="form-control" type="number" name="year" value="{{ $year }}" min="2020" max="2100">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ay</label>
                            <select class="form-select" name="month">
                                <option value="">Tüm yıl</option>
                                @foreach ($months as $number => $name)
                                    <option value="{{ $number }}" @selected($month === $number)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button class="btn" type="submit"><x-icon name="filter" class="me-1" /> Raporu Getir</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row row-cards mb-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card metric-card"><div class="card-body"><div class="text-secondary">{{ $month ? 'Dönem Tahakkuku' : 'Yıllık Tahakkuk' }}</div><div class="h1">{{ $money($report['totals']['due']) }}</div><div class="text-secondary">Toplam borçlandırma</div></div></div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card metric-card"><div class="card-body"><div class="text-secondary">{{ $month ? 'Dönem Tahsilatı' : 'Yıllık Tahsilat' }}</div><div class="h1 text-success">{{ $money($report['totals']['paid']) }}</div><div class="text-secondary">%{{ $report['collection_rate'] }} tahsilat</div></div></div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card metric-card"><div class="card-body"><div class="text-secondary">{{ $month ? 'Dönem Gideri' : 'Yıllık Gider' }}</div><div class="h1 text-warning">{{ $money($report['totals']['expense']) }}</div><div class="text-secondary">Kayıtlı gider toplamı</div></div></div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card metric-card"><div class="card-body"><div class="text-secondary">Net Kasa Etkisi</div><div class="h1 {{ $report['totals']['balance'] >= 0 ? 'text-primary' : 'text-danger' }}">{{ $money($report['totals']['balance']) }}</div><div class="text-secondary">Tahsilat - gider</div></div></div>
                </div>
            </div>

            <div class="row row-cards">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Aylık Grafik</h3></div>
                        <div class="card-body">
                            <div class="report-chart">
                                @foreach ($report['months'] as $month)
                                    <div class="report-chart-row">
                                        <div class="report-chart-label">{{ $month['name'] }}</div>
                                        <div class="report-chart-track">
                                            <span class="report-bar report-bar-due" style="width: {{ $month['due'] > 0 ? max(2, ($month['due'] / $report['max']) * 100) : 0 }}%"></span>
                                            <span class="report-bar report-bar-paid" style="width: {{ $month['paid'] > 0 ? max(2, ($month['paid'] / $report['max']) * 100) : 0 }}%"></span>
                                            <span class="report-bar report-bar-expense" style="width: {{ $month['expense'] > 0 ? max(2, ($month['expense'] / $report['max']) * 100) : 0 }}%"></span>
                                        </div>
                                        <div class="report-chart-value">%{{ $month['rate'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex gap-3 mt-3 text-secondary small">
                                <span><span class="legend-dot bg-primary"></span> Tahakkuk</span>
                                <span><span class="legend-dot bg-success"></span> Tahsilat</span>
                                <span><span class="legend-dot bg-warning"></span> Gider</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Yıl Özeti</h3></div>
                        <div class="card-body text-center">
                            <div class="collection-ring dashboard-ring" style="--rate: {{ $report['collection_rate'] }}%;">
                                <div class="collection-ring-inner">
                                    <div><strong>%{{ $report['collection_rate'] }}</strong><span class="text-secondary">Tahsilat</span></div>
                                </div>
                            </div>
                            <div class="text-secondary mt-3">{{ $periodLabel }} için kalan borç {{ $money($report['totals']['pending']) }}.</div>
                            <div class="list-group list-group-flush mt-3 text-start">
                                @foreach ($report['totals']['sources'] as $source)
                                    <div class="list-group-item px-0 d-flex justify-content-between">
                                        <span class="text-secondary">{{ $source['label'] }}</span>
                                        <strong>{{ $money($source['amount']) }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Aylık Detay</h3></div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Ay</th>
                                        <th>Tahakkuk</th>
                                        <th>Tahsilat</th>
                                        <th>Gider</th>
                                        <th>Kalan Borç</th>
                                        <th>Net</th>
                                        <th>Oran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report['months'] as $month)
                                        <tr>
                                            <td class="fw-bold">{{ $month['name'] }}</td>
                                            <td>{{ $money($month['due']) }}</td>
                                            <td class="text-success">{{ $money($month['paid']) }}</td>
                                            <td class="text-warning">{{ $money($month['expense']) }}</td>
                                            <td class="text-danger">{{ $money($month['pending']) }}</td>
                                            <td class="{{ $month['balance'] >= 0 ? 'text-primary' : 'text-danger' }}">{{ $money($month['balance']) }}</td>
                                            <td><span class="badge bg-blue-lt">%{{ $month['rate'] }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
