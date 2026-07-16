@php
    $money = fn ($value) => '₺' . number_format((float) $value, 2, ',', '.');
    $statusBadge = [
        'matched' => 'bg-green-lt',
        'manual_matched' => 'bg-blue-lt',
        'unmatched' => 'bg-yellow-lt',
        'needs_review' => 'bg-orange-lt',
        'failed' => 'bg-red-lt',
    ];
    $statusLabel = [
        'matched' => 'Otomatik işlendi',
        'manual_matched' => 'Manuel işlendi',
        'unmatched' => 'Eşleşme yok',
        'needs_review' => 'İnceleme gerekli',
        'failed' => 'Başarısız',
    ];
@endphp

<div class="table-responsive">
    <table class="table table-vcenter card-table">
        <thead>
            <tr>
                <th>Tarih</th>
                <th>Gönderen</th>
                <th>Açıklama</th>
                <th>Tutar</th>
                <th>Durum</th>
                <th>Sonuç</th>
                @if ($showManualAction)
                    <th class="w-25">Manuel İşlem</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $transaction)
                @php
                    $manualRowId = 'manual-match-' . $transaction->id;
                    $warnings = $nameWarnings[$transaction->id] ?? [];
                @endphp
                <tr>
                    <td>
                        <div class="fw-bold">{{ $transaction->transaction_date->format('d.m.Y') }}</div>
                        <div class="text-secondary small">{{ $transaction->transaction_date->format('H:i') }} · {{ $transaction->operation_no ?? $transaction->bank_transaction_id }}</div>
                    </td>
                    <td>
                        <div class="fw-bold">{{ $transaction->sender_name ?? '-' }}</div>
                        <div class="text-secondary small">{{ $transaction->sender_iban ?? 'IBAN yok' }}</div>
                    </td>
                    <td class="text-wrap" style="max-width: 20rem;">{{ $transaction->description ?? '-' }}</td>
                    <td class="fw-bold">{{ $money($transaction->amount) }}</td>
                    <td><span class="badge {{ $statusBadge[$transaction->status] ?? 'bg-secondary-lt' }}">{{ $statusLabel[$transaction->status] ?? $transaction->status }}</span></td>
                    <td class="text-wrap" style="max-width: 18rem;">
                        @if ($transaction->payment)
                            <div class="fw-bold text-success">{{ $transaction->payment->receipt_no }}</div>
                            <div class="text-secondary small">{{ $transaction->due?->apartment?->number }} · {{ $transaction->match_reason }}</div>
                        @else
                            @if (! empty($warnings))
                                <div class="badge bg-yellow-lt mb-1">
                                    İsim benzerliği var, otomatik işlenmedi
                                </div>
                            @endif
                            <div class="text-danger">{{ $transaction->failure_reason ?? 'Aidat kaydıyla eşleşmedi.' }}</div>
                        @endif
                    </td>
                    @if ($showManualAction)
                        <td>
                            <button class="btn btn-sm btn-primary" type="button" data-manual-match-toggle="{{ $manualRowId }}">
                                <x-icon name="git-compare" class="me-1" /> Manuel Eşleştir
                            </button>
                        </td>
                    @endif
                </tr>
                @if ($showManualAction)
                    <tr class="manual-match-row d-none" id="{{ $manualRowId }}">
                        <td colspan="7">
                            <div class="manual-match-panel">
                                @if (! empty($warnings))
                                    <div class="alert alert-warning mb-3">
                                        <x-icon name="alert-triangle" class="me-2" />
                                        Gönderen adı sistemdeki sakin adıyla benziyor. Sistem bu nedenle otomatik işlem yapmadı; kontrol ederek manuel onaylayın.
                                        <div class="mt-2 small">
                                            @foreach ($warnings as $warning)
                                                @php $resident = $warning['resident']; @endphp
                                                <span class="badge bg-yellow-lt me-1">
                                                    {{ $resident->full_name }} · {{ $resident->apartment->buildingBlock->name }} / {{ $resident->apartment->number }} · %{{ round($warning['score']) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <form class="row g-2 align-items-end" method="post" action="{{ route('integrations.vakifbank.transactions.approve', $transaction) }}">
                                    @csrf
                                    <div class="col-lg-9">
                                        <label class="form-label">Bu banka hareketini hangi aidata işleyeceksiniz?</label>
                                        <select class="form-select" name="due_id" required>
                                            <option value="">Aidat seç</option>
                                            @foreach ($dues as $due)
                                                @php $pending = max(0, (float) $due->amount - (float) $due->payments->sum('amount')); @endphp
                                                <option value="{{ $due->id }}">
                                                    {{ $due->apartment->buildingBlock->name }} / {{ $due->apartment->number }} - {{ $due->apartment->activeResident?->full_name ?? 'Sakin yok' }} - {{ $due->period_month }}/{{ $due->period_year }} - Kalan {{ $money($pending) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <button class="btn btn-primary w-100" type="submit"><x-icon name="checks" class="me-1" /> Aidata Kaydet</button>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="{{ $showManualAction ? 7 : 6 }}" class="text-center text-secondary py-5">Bu sekmede işlem yok.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="card-footer">{{ $rows->links() }}</div>
