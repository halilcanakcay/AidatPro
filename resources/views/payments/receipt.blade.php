@extends('layouts.app', ['title' => 'Makbuz - AidatPro'])

@php
    $money = fn ($value) => '₺' . number_format((float) $value, 2, ',', '.');
    $phone = preg_replace('/\D+/', '', (string) $payment->due->apartment->activeResident?->phone);
    $phone = str_starts_with($phone, '0') ? '9' . $phone : $phone;
    $whatsappUrl = 'https://wa.me/' . $phone . '?text=' . rawurlencode($whatsappMessage);
@endphp

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Ödeme makbuzu</div>
                    <h2 class="page-title">{{ $payment->receipt_no }}</h2>
                    <div class="text-secondary mt-1">{{ $payment->due->apartment->buildingBlock->name }} / {{ $payment->due->apartment->number }}</div>
                </div>
                <div class="col-auto">
                    <div class="btn-list">
                        <a class="btn" href="{{ route('payments.index') }}"><x-icon name="arrow-left" class="me-1" /> Ödemeler</a>
                        <a class="btn btn-primary" href="{{ route('payments.receipt.pdf', $payment) }}"><x-icon name="download" class="me-1" /> PDF İndir</a>
                        @if ($phone)
                            <a class="btn btn-success" href="{{ $whatsappUrl }}" target="_blank" rel="noopener"><x-icon name="brand-whatsapp" class="me-1" /> WhatsApp</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @if (session('status'))
                <div class="alert alert-success"><x-icon name="circle-check" class="me-2" />{{ session('status') }}</div>
            @endif

            <div class="receipt-preview">
                <div class="receipt-paper">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h1 class="mb-1">{{ $site->name }}</h1>
                            <div class="text-secondary">{{ $site->address }}</div>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-primary-lt">Makbuz</div>
                            <div class="h3 mt-2">{{ $payment->receipt_no }}</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="receipt-box">
                                <div class="text-secondary">Sakin</div>
                                <div class="fw-bold">{{ $payment->due->apartment->activeResident?->full_name ?? '-' }}</div>
                                <div>{{ $payment->due->apartment->buildingBlock->name }} / {{ $payment->due->apartment->number }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="receipt-box">
                                <div class="text-secondary">Ödeme</div>
                                <div class="fw-bold">{{ $payment->paid_at->format('d.m.Y H:i') }}</div>
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    <span class="badge bg-secondary-lt">{{ strtoupper($payment->method) }}</span>
                                    <span class="badge {{ $payment->source_badge_class }}">{{ $payment->source_label }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Açıklama</th>
                                <th class="text-end">Tutar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $payment->due->period_month }}/{{ $payment->due->period_year }} dönem aidat ödemesi</td>
                                <td class="text-end fw-bold">{{ $money($payment->amount) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="text-end mt-4">
                        <div class="text-secondary">Tahsil edilen</div>
                        <div class="display-6 fw-bold">{{ $money($payment->amount) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
