<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\BuildingBlock;
use App\Models\Due;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Site;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $periodYear = (int) request('year', 2026);
        $periodMonth = max(1, min(12, (int) request('month', 7)));
        $sites = Site::query()->orderBy('name')->get();
        $site = Site::query()->find(request('site_id')) ?? $sites->firstOrFail();
        $blocks = BuildingBlock::query()->where('site_id', $site->id)->orderBy('name')->get();
        $blockId = $blocks->firstWhere('id', request()->integer('block_id'))?->id;

        $apartmentStats = DB::table('apartments')
            ->join('building_blocks', 'building_blocks.id', '=', 'apartments.building_block_id')
            ->where('building_blocks.site_id', $site->id)
            ->when($blockId, fn ($query) => $query->where('building_blocks.id', $blockId))
            ->selectRaw('COUNT(*) as total, SUM(status = "occupied") as occupied, SUM(status = "empty") as empty_count')
            ->first();

        $paymentSubquery = DB::table('payments')
            ->selectRaw('due_id, SUM(amount) as paid_amount')
            ->groupBy('due_id');

        $summary = DB::table('dues')
            ->join('apartments', 'apartments.id', '=', 'dues.apartment_id')
            ->join('building_blocks', 'building_blocks.id', '=', 'apartments.building_block_id')
            ->leftJoinSub($paymentSubquery, 'payment_totals', 'payment_totals.due_id', '=', 'dues.id')
            ->where('building_blocks.site_id', $site->id)
            ->when($blockId, fn ($query) => $query->where('building_blocks.id', $blockId))
            ->where('dues.period_year', $periodYear)
            ->where('dues.period_month', $periodMonth)
            ->selectRaw('
                COUNT(dues.id) as due_count,
                COALESCE(SUM(dues.amount), 0) as total_due,
                COALESCE(SUM(COALESCE(payment_totals.paid_amount, 0)), 0) as total_paid,
                COALESCE(SUM(CASE WHEN dues.amount - COALESCE(payment_totals.paid_amount, 0) > 0 THEN dues.amount - COALESCE(payment_totals.paid_amount, 0) ELSE 0 END), 0) as total_pending,
                SUM(CASE WHEN COALESCE(payment_totals.paid_amount, 0) >= dues.amount THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN COALESCE(payment_totals.paid_amount, 0) > 0 AND COALESCE(payment_totals.paid_amount, 0) < dues.amount THEN 1 ELSE 0 END) as partial_count,
                SUM(CASE WHEN COALESCE(payment_totals.paid_amount, 0) = 0 THEN 1 ELSE 0 END) as unpaid_count
            ')
            ->first();

        $dues = Due::query()
            ->with(['apartment.buildingBlock', 'apartment.activeResident', 'payments.bankTransaction'])
            ->where('period_year', $periodYear)
            ->where('period_month', $periodMonth)
            ->whereHas('apartment.buildingBlock', fn ($query) => $query->where('site_id', $site->id))
            ->when($blockId, fn ($query) => $query->whereHas('apartment', fn ($apartment) => $apartment->where('building_block_id', $blockId)))
            ->limit(10)
            ->get()
            ->sortBy(fn (Due $due) => $due->apartment->buildingBlock->name . $due->apartment->number)
            ->values();

        $followItems = $dues
            ->map(function (Due $due) {
                $paid = (float) $due->payments->sum('amount');

                return [
                    'due' => $due,
                    'pending' => max(0, (float) $due->amount - $paid),
                ];
            })
            ->filter(fn (array $item) => $item['pending'] > 0)
            ->sortByDesc('pending')
            ->take(4)
            ->values();

        $incomeTotal = Payment::query()
            ->whereHas('due.apartment.buildingBlock', fn ($query) => $query->where('site_id', $site->id))
            ->when($blockId, fn ($query) => $query->whereHas('due.apartment', fn ($apartment) => $apartment->where('building_block_id', $blockId)))
            ->sum('amount');
        $expenseTotal = Expense::query()->where('site_id', $site->id)->sum('amount');
        $todayPayments = Payment::query()
            ->whereHas('due.apartment.buildingBlock', fn ($query) => $query->where('site_id', $site->id))
            ->when($blockId, fn ($query) => $query->whereHas('due.apartment', fn ($apartment) => $apartment->where('building_block_id', $blockId)))
            ->whereDate('paid_at', today());
        $collectionRate = (float) $summary->total_due > 0
            ? min(100, (int) round(((float) $summary->total_paid / (float) $summary->total_due) * 100))
            : 0;

        return view('dashboard', [
            'site' => $site,
            'sites' => $sites,
            'blocks' => $blocks,
            'blockId' => $blockId,
            'periodYear' => $periodYear,
            'periodMonth' => $periodMonth,
            'periodName' => $this->monthName($periodMonth) . ' ' . $periodYear,
            'apartmentStats' => $apartmentStats,
            'summary' => $summary,
            'collectionRate' => $collectionRate,
            'dues' => $dues,
            'followItems' => $followItems,
            'cashBalance' => (float) $incomeTotal - (float) $expenseTotal,
            'todayPaymentTotal' => (clone $todayPayments)->sum('amount'),
            'todayPaymentCount' => (clone $todayPayments)->count(),
            'recentPayments' => Payment::with(['bankTransaction', 'due.apartment.buildingBlock', 'due.apartment.activeResident'])
                ->whereHas('due.apartment.buildingBlock', fn ($query) => $query->where('site_id', $site->id))
                ->when($blockId, fn ($query) => $query->whereHas('due.apartment', fn ($apartment) => $apartment->where('building_block_id', $blockId)))
                ->latest('paid_at')
                ->limit(5)
                ->get(),
            'expenses' => Expense::where('site_id', $site->id)->latest('expense_date')->limit(5)->get(),
            'announcements' => Announcement::where('site_id', $site->id)->latest('publish_date')->limit(3)->get(),
            'auditLogs' => AuditLog::with('user')->latest()->limit(4)->get(),
            'months' => $this->months(),
        ]);
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

    private function monthName(int $month): string
    {
        return $this->months()[$month] ?? 'Bilinmeyen';
    }
}
