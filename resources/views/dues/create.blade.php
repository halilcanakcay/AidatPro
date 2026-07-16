@extends('layouts.app', ['title' => 'Tahakkuk Oluştur - AidatPro'])

@php
    $defaultAmount = old('amount', 2450);
    $selectedMonth = (int) old('period_month', 8);
    $selectedTarget = old('target', 'occupied');
    $targetCount = $selectedTarget === 'all' ? (int) $apartmentStats->total : (int) $apartmentStats->occupied;
    $estimatedTotal = (float) $defaultAmount * $targetCount;
    $money = fn ($value) => '₺' . number_format((float) $value, 2, ',', '.');
@endphp

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Tahakkuk yönetimi</div>
                    <h2 class="page-title">Aidat Tahakkuku Oluştur</h2>
                    <div class="text-secondary mt-1">{{ $site->name }} için dönem bazlı toplu aidat kaydı hazırlayın.</div>
                </div>
                <div class="col-auto ms-auto">
                    <a class="btn" href="{{ route('dashboard') }}">
                        <x-icon name="arrow-left" class=" me-1" /> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="form-shell">
                <form class="card" method="post" action="{{ route('dues.store') }}">
                    @csrf

                    <div class="card-header">
                        <div>
                            <h3 class="card-title">Dönem ve Kapsam</h3>
                            <p class="card-subtitle">Aynı daire ve dönem için mevcut kayıtlar korunur, sadece eksikler oluşturulur.</p>
                        </div>
                        <div class="card-actions">
                            <span class="badge bg-blue-lt">Toplu işlem</span>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        @if (session('status'))
                            <div class="p-3 border-bottom">
                                <div class="alert alert-success mb-0" role="alert">
                                    <div class="d-flex">
                                        <div><x-icon name="circle-check" class=" icon alert-icon" /></div>
                                        <div>{{ session('status') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="p-3 border-bottom">
                                <div class="alert alert-danger mb-0" role="alert">
                                    <div class="d-flex">
                                        <div><x-icon name="alert-circle" class=" icon alert-icon" /></div>
                                        <div>{{ $errors->first() }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <section class="form-section">
                            <div class="section-kicker">
                                <span class="avatar bg-primary-lt"><x-icon name="calendar-month" class="" /></span>
                                <div>
                                    <div class="fw-bold">Dönem Bilgisi</div>
                                    <div class="text-secondary small">Yıl, ay ve son ödeme tarihi</div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Yıl</label>
                                    <input class="form-control" type="number" name="period_year" min="2020" max="2100" value="{{ old('period_year', 2026) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Ay</label>
                                    <select class="form-select" name="period_month" required>
                                        @foreach ($months as $number => $name)
                                            <option value="{{ $number }}" @selected($selectedMonth === $number)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Son Ödeme Tarihi</label>
                                    <input class="form-control" type="date" name="due_date" value="{{ old('due_date', '2026-08-25') }}" required>
                                </div>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-kicker">
                                <span class="avatar bg-teal-lt"><x-icon name="cash-banknote" class="" /></span>
                                <div>
                                    <div class="fw-bold">Tutar ve Kapsam</div>
                                    <div class="text-secondary small">Borçlandırılacak daire grubu</div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Aidat Tutarı</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₺</span>
                                        <input class="form-control" type="number" name="amount" min="1" step="0.01" value="{{ $defaultAmount }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Daire Kapsamı</label>
                                    <select class="form-select" name="target">
                                        <option value="occupied" @selected($selectedTarget === 'occupied')>Sadece dolu daireler</option>
                                        <option value="all" @selected($selectedTarget === 'all')>Tüm daireler</option>
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-kicker">
                                <span class="avatar bg-indigo-lt"><x-icon name="notes" class="" /></span>
                                <div>
                                    <div class="fw-bold">Açıklama</div>
                                    <div class="text-secondary small">Makbuz ve hesap hareketi notu</div>
                                </div>
                            </div>

                            <div>
                                <label class="form-label">Not</label>
                                <textarea class="form-control" name="note" rows="4" placeholder="Örn: {{ $months[$selectedMonth] }} aidatı">{{ old('note') }}</textarea>
                            </div>
                        </section>
                    </div>

                    <div class="card-footer text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a class="btn" href="{{ route('dashboard') }}">Vazgeç</a>
                            <button class="btn btn-primary" type="submit">
                                <x-icon name="check" class=" me-1" /> Tahakkuku Oluştur
                            </button>
                        </div>
                    </div>
                </form>

                <aside class="sticky-side">
                    <div class="row row-cards">
                        <div class="col-12">
                            <div class="card bg-primary text-primary-fg">
                                <div class="card-body">
                                    <div class="badge bg-white text-primary mb-3">
                                        <x-icon name="shield-check" class=" me-1" /> İşlem özeti
                                    </div>
                                    <h3 class="card-title text-white mb-2">{{ $months[$selectedMonth] }} 2026</h3>
                                    <p class="mb-0 opacity-75">Oluşturulan tahakkuklar dönem bakiyesi ve tahsilat ekranlarına yansır.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex justify-content-between">
                                        <span class="text-secondary">Kapsam</span>
                                        <strong>{{ $targetCount }} daire</strong>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between">
                                        <span class="text-secondary">Birim tutar</span>
                                        <strong>{{ $money($defaultAmount) }}</strong>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between">
                                        <span class="text-secondary">Tahmini tahakkuk</span>
                                        <strong class="text-primary">{{ $money($estimatedTotal) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h3 class="card-title">Kontrol Listesi</h3></div>
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item"><x-icon name="circle-check" class=" text-success me-2" />Tekrarlı dönem kayıtları atlanır.</div>
                                    <div class="list-group-item"><x-icon name="circle-check" class=" text-success me-2" />İşlem güvenlik günlüğüne yazılır.</div>
                                    <div class="list-group-item"><x-icon name="circle-check" class=" text-success me-2" />Dönem bakiyesi dashboard’da görünür.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection
