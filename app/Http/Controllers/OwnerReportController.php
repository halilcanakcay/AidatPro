<?php

namespace App\Http\Controllers;

use App\Models\Due;
use App\Models\OwnerReportLink;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerReportController extends Controller
{
    public function show(Request $request, string $token): View
    {
        $link = OwnerReportLink::query()
            ->with(['apartment.buildingBlock.site', 'apartment.activeResident'])
            ->where('token', $token)
            ->firstOrFail();

        if ($link->is_expired) {
            abort(410, 'Bu rapor bağlantısının süresi dolmuş.');
        }

        $link->update(['last_used_at' => now()]);

        $year = (int) $request->query('year', now()->year);
        $report = $this->apartmentReport($link, $year);

        return view('owner-reports.show', compact('link', 'year', 'report'));
    }

    private function apartmentReport(OwnerReportLink $link, int $year): array
    {
        $dues = Due::query()
            ->with('payments.bankTransaction')
            ->where('apartment_id', $link->apartment_id)
            ->where('period_year', $year)
            ->orderBy('period_month')
            ->get()
            ->keyBy('period_month');

        $months = [];
        $totals = ['due' => 0.0, 'paid' => 0.0, 'pending' => 0.0];

        foreach ($this->months() as $number => $name) {
            $due = $dues->get($number);
            $amount = $due ? (float) $due->amount : 0.0;
            $paid = $due ? (float) $due->payments->sum('amount') : 0.0;
            $pending = max(0, $amount - $paid);

            $months[$number] = [
                'name' => $name,
                'due' => $amount,
                'paid' => $paid,
                'pending' => $pending,
                'status' => $amount === 0.0 ? 'Kayıt yok' : ($pending <= 0 ? 'Ödendi' : ($paid > 0 ? 'Kısmi' : 'Bekliyor')),
                'source' => $paid > 0 ? $due?->paymentSourceLabel() : null,
                'source_class' => $paid > 0 ? $due?->paymentSourceBadgeClass() : 'bg-secondary-lt',
                'rate' => $amount > 0 ? min(100, (int) round(($paid / $amount) * 100)) : 0,
            ];

            $totals['due'] += $amount;
            $totals['paid'] += $paid;
            $totals['pending'] += $pending;
        }

        return [
            'months' => $months,
            'totals' => $totals,
            'collection_rate' => $totals['due'] > 0 ? min(100, (int) round(($totals['paid'] / $totals['due']) * 100)) : 0,
        ];
    }

    private function months(): array
    {
        return [
            1 => 'Ocak',
            2 => 'Şubat',
            3 => 'Mart',
            4 => 'Nisan',
            5 => 'Mayıs',
            6 => 'Haziran',
            7 => 'Temmuz',
            8 => 'Ağustos',
            9 => 'Eylül',
            10 => 'Ekim',
            11 => 'Kasım',
            12 => 'Aralık',
        ];
    }
}
