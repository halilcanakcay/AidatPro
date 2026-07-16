@extends('layouts.app', ['title' => 'Aidatlar - AidatPro'])

@php
    $money = fn ($value) => '₺' . number_format((float) $value, 2, ',', '.');
    $periodName = $months[$periodMonth] . ' ' . $periodYear;
@endphp

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Tahakkuk ve takip</div>
                    <h2 class="page-title">Aidatlar</h2>
                    <div class="text-secondary mt-1">{{ $periodName }} dönemi borç ve ödeme durumu.</div>
                </div>
                <div class="col-auto">
                    <div class="btn-list">
                        <a class="btn" href="{{ route('payments.create') }}"><x-icon name="wallet" class=" me-1" /> Ödeme Al</a>
                        <a class="btn btn-primary" href="{{ route('dues.create') }}"><x-icon name="plus" class=" me-1" /> Tahakkuk Oluştur</a>
                    </div>
                </div>
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
                    <h3 class="card-title">Aidat Listesi</h3>
                    <div class="card-actions">
                        <form class="row g-2" method="get" action="{{ route('dues.index') }}">
                            <div class="col-auto">
                                <select class="form-select" name="site_id" aria-label="Site seç">
                                    @foreach ($sites as $filterSite)
                                        <option value="{{ $filterSite->id }}" @selected($site->id === $filterSite->id)>{{ $filterSite->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <select class="form-select" name="block_id" aria-label="Blok seç">
                                    <option value="">Tüm bloklar</option>
                                    @foreach ($blocks as $block)
                                        <option value="{{ $block->id }}" @selected($blockId === $block->id)>{{ $block->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <input class="form-control" type="number" name="year" value="{{ $periodYear }}" min="2020" max="2100">
                            </div>
                            <div class="col-auto">
                                <select class="form-select" name="month">
                                    @foreach ($months as $number => $name)
                                        <option value="{{ $number }}" @selected($periodMonth === $number)>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <select class="form-select" name="status">
                                    <option value="">Tüm durumlar</option>
                                    <option value="paid" @selected($status === 'paid')>Ödendi</option>
                                    <option value="partial" @selected($status === 'partial')>Kısmi</option>
                                    <option value="waiting" @selected($status === 'waiting')>Bekliyor</option>
                                    <option value="overdue" @selected($status === 'overdue')>Gecikti</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <input class="form-control" name="search" value="{{ $search }}" placeholder="Daire veya sakin ara">
                            </div>
                            <div class="col-auto"><button class="btn" type="submit"><x-icon name="filter" class="" /></button></div>
                        </form>
                    </div>
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
                                <th>Son Gün</th>
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
                                $statusClass = $pending <= 0 ? 'bg-green-lt' : ($paid > 0 ? 'bg-yellow-lt' : 'bg-red-lt');
                                $statusLabel = $pending <= 0 ? 'Ödendi' : ($paid > 0 ? 'Kısmi' : 'Bekliyor');
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
                                <td>{{ $due->due_date->format('d.m.Y') }}</td>
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
                                    <a class="btn btn-sm" href="{{ route('dues.edit', $due) }}">Düzenle</a>
                                    <form class="d-inline" method="post" action="{{ route('dues.destroy', $due) }}" data-confirm="Aidat kaydı silinsin mi?">
                                        @csrf
                                        @method('delete')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Sil</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-secondary py-5">Bu dönem için aidat bulunamadı.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $dues->links() }}</div>
            </div>
        </div>
    </div>
@endsection
