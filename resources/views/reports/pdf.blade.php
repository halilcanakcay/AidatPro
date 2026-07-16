@php
    $money = fn ($value) => '₺' . number_format((float) $value, 2, ',', '.');
@endphp
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { margin: 0 0 4px; font-size: 22px; }
        .muted { color: #64748b; }
        .summary { width: 100%; margin: 18px 0; border-collapse: collapse; }
        .summary td { width: 25%; padding: 12px; border: 1px solid #dbe3ef; }
        .label { color: #64748b; font-size: 11px; }
        .value { display: block; margin-top: 6px; font-size: 18px; font-weight: bold; }
        table.detail { width: 100%; border-collapse: collapse; }
        table.detail th, table.detail td { padding: 8px; border: 1px solid #dbe3ef; text-align: left; }
        table.detail th { background: #f1f5f9; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $site->name }} - {{ $periodLabel }} Finansal Rapor</h1>
    <div class="muted">Tahakkuk, tahsilat, gider ve bakiye özeti</div>

    <table class="summary">
        <tr>
            <td><span class="label">{{ $month ? 'Dönem Tahakkuku' : 'Yıllık Tahakkuk' }}</span><span class="value">{{ $money($report['totals']['due']) }}</span></td>
            <td><span class="label">{{ $month ? 'Dönem Tahsilatı' : 'Yıllık Tahsilat' }}</span><span class="value">{{ $money($report['totals']['paid']) }}</span></td>
            <td><span class="label">{{ $month ? 'Dönem Gideri' : 'Yıllık Gider' }}</span><span class="value">{{ $money($report['totals']['expense']) }}</span></td>
            <td><span class="label">Net Kasa Etkisi</span><span class="value">{{ $money($report['totals']['balance']) }}</span></td>
        </tr>
    </table>

    <table class="detail">
        <thead>
            <tr>
                <th>Ay</th>
                <th class="right">Tahakkuk</th>
                <th class="right">Tahsilat</th>
                <th class="right">Gider</th>
                <th class="right">Kalan Borç</th>
                <th class="right">Net</th>
                <th class="right">Tahsilat Oranı</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report['months'] as $month)
                <tr>
                    <td>{{ $month['name'] }}</td>
                    <td class="right">{{ $money($month['due']) }}</td>
                    <td class="right">{{ $money($month['paid']) }}</td>
                    <td class="right">{{ $money($month['expense']) }}</td>
                    <td class="right">{{ $money($month['pending']) }}</td>
                    <td class="right">{{ $money($month['balance']) }}</td>
                    <td class="right">%{{ $month['rate'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
