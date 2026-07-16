<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 13px; }
        .header { display: table; width: 100%; margin-bottom: 30px; }
        .left, .right { display: table-cell; vertical-align: top; }
        .right { text-align: right; }
        h1 { margin: 0 0 5px; font-size: 24px; }
        .muted { color: #6b7280; }
        .box { border: 1px solid #d1d5db; padding: 14px; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #d1d5db; padding: 10px; text-align: left; }
        .text-end { text-align: right; }
        .total { margin-top: 35px; text-align: right; font-size: 22px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="left">
            <h1>{{ $site->name }}</h1>
            <div class="muted">{{ $site->address }}</div>
        </div>
        <div class="right">
            <div>MAKBUZ</div>
            <h1>{{ $payment->receipt_no }}</h1>
        </div>
    </div>

    <div class="box">
        <strong>Sakin:</strong> {{ $payment->due->apartment->activeResident?->full_name ?? '-' }}<br>
        <strong>Daire:</strong> {{ $payment->due->apartment->buildingBlock->name }} / {{ $payment->due->apartment->number }}<br>
        <strong>Tarih:</strong> {{ $payment->paid_at->format('d.m.Y H:i') }}<br>
        <strong>Yöntem:</strong> {{ strtoupper($payment->method) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Açıklama</th>
                <th class="text-end">Tutar</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $payment->due->period_month }}/{{ $payment->due->period_year }} dönem aidat ödemesi</td>
                <td class="text-end">₺{{ number_format((float) $payment->amount, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total">₺{{ number_format((float) $payment->amount, 2, ',', '.') }}</div>
</body>
</html>
