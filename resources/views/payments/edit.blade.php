@extends('layouts.app', ['title' => 'Ödeme Düzenle - AidatPro'])

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Tahsilat yönetimi</div>
                    <h2 class="page-title">{{ $payment->receipt_no }} Makbuzunu Düzenle</h2>
                    <div class="text-secondary mt-1">Ödeme tutarı, yöntemi ve tarihini güncelleyin.</div>
                </div>
                <div class="col-auto"><a class="btn" href="{{ route('payments.index') }}"><x-icon name="arrow-left" class="me-1" /> Listeye Dön</a></div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form class="card" method="post" action="{{ route('payments.update', $payment) }}">
                @csrf
                @method('put')
                <div class="card-header"><h3 class="card-title">Ödeme Bilgileri</h3></div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger"><x-icon name="alert-circle" class="me-2" />{{ $errors->first() }}</div>
                    @endif
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Borç</label>
                            <select class="form-select" name="due_id" required>
                                @foreach ($dues as $due)
                                    <option value="{{ $due->id }}" @selected(old('due_id', $payment->due_id) == $due->id)>
                                        {{ $due->apartment->buildingBlock->name }} / {{ $due->apartment->number }} - {{ $due->period_month }}/{{ $due->period_year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tutar</label>
                            <div class="input-group">
                                <span class="input-group-text">₺</span>
                                <input class="form-control" type="number" name="amount" min="0.01" step="0.01" value="{{ old('amount', $payment->amount) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Yöntem</label>
                            <select class="form-select" name="method">
                                @foreach (['bank' => 'Banka', 'eft' => 'EFT/Havale', 'card' => 'Kart', 'cash' => 'Nakit'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('method', $payment->method) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ödeme Tarihi</label>
                            <input class="form-control" type="datetime-local" name="paid_at" value="{{ old('paid_at', $payment->paid_at->format('Y-m-d\\TH:i')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Makbuz No</label>
                            <input class="form-control" name="receipt_no" value="{{ old('receipt_no', $payment->receipt_no) }}" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a class="btn" href="{{ route('payments.index') }}">Vazgeç</a>
                    <button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> Güncelle</button>
                </div>
            </form>
        </div>
    </div>
@endsection
