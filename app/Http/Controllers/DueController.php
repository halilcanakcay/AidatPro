<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\AuditLog;
use App\Models\BuildingBlock;
use App\Models\Due;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DueController extends Controller
{
    public function index(Request $request): View
    {
        $sites = Site::query()->orderBy('name')->get();
        $site = Site::query()->find($request->query('site_id')) ?? $sites->firstOrFail();
        $blocks = BuildingBlock::query()->where('site_id', $site->id)->orderBy('name')->get();
        $blockId = $blocks->firstWhere('id', $request->integer('block_id'))?->id;
        $periodYear = (int) $request->query('year', 2026);
        $periodMonth = max(1, min(12, (int) $request->query('month', 7)));
        $status = (string) $request->query('status', '');
        $search = trim((string) $request->query('search', ''));
        $paymentSubquery = DB::table('payments')
            ->selectRaw('due_id, SUM(amount) as paid_amount')
            ->groupBy('due_id');

        $dues = Due::query()
            ->with(['apartment.buildingBlock', 'apartment.activeResident', 'payments.bankTransaction'])
            ->where('period_year', $periodYear)
            ->where('period_month', $periodMonth)
            ->whereHas('apartment.buildingBlock', fn ($query) => $query->where('site_id', $site->id))
            ->when($blockId, fn ($query) => $query->whereHas('apartment', fn ($apartment) => $apartment->where('building_block_id', $blockId)))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('apartment', fn ($apartment) => $apartment->where('number', 'like', "%{$search}%"))
                        ->orWhereHas('apartment.activeResident', fn ($resident) => $resident->where('full_name', 'like', "%{$search}%"));
                });
            })
            ->join('apartments', 'apartments.id', '=', 'dues.apartment_id')
            ->join('building_blocks', 'building_blocks.id', '=', 'apartments.building_block_id')
            ->leftJoinSub($paymentSubquery, 'payment_totals', 'payment_totals.due_id', '=', 'dues.id')
            ->when($status === 'paid', fn ($query) => $query->whereRaw('COALESCE(payment_totals.paid_amount, 0) >= dues.amount'))
            ->when($status === 'partial', fn ($query) => $query->whereRaw('COALESCE(payment_totals.paid_amount, 0) > 0 AND COALESCE(payment_totals.paid_amount, 0) < dues.amount'))
            ->when($status === 'waiting', fn ($query) => $query->whereRaw('COALESCE(payment_totals.paid_amount, 0) = 0 AND dues.due_date >= ?', [today()->toDateString()]))
            ->when($status === 'overdue', fn ($query) => $query->whereRaw('COALESCE(payment_totals.paid_amount, 0) < dues.amount AND dues.due_date < ?', [today()->toDateString()]))
            ->orderBy('building_blocks.name')
            ->orderBy('apartments.number')
            ->select('dues.*')
            ->paginate(12)
            ->withQueryString();

        return view('dues.index', [
            'site' => $site,
            'sites' => $sites,
            'blocks' => $blocks,
            'blockId' => $blockId,
            'dues' => $dues,
            'months' => $this->months(),
            'periodYear' => $periodYear,
            'periodMonth' => $periodMonth,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        $site = Site::query()->firstOrFail();
        $apartmentStats = Apartment::query()
            ->whereHas('buildingBlock', fn ($query) => $query->where('site_id', $site->id))
            ->selectRaw('COUNT(*) as total, SUM(status = "occupied") as occupied')
            ->first();

        return view('dues.create', [
            'site' => $site,
            'apartmentStats' => $apartmentStats,
            'months' => $this->months(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_year' => ['required', 'integer', 'between:2020,2100'],
            'period_month' => ['required', 'integer', 'between:1,12'],
            'amount' => ['required', 'numeric', 'min:1'],
            'due_date' => ['required', 'date'],
            'target' => ['required', 'in:occupied,all'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $site = Site::query()->firstOrFail();

        $created = DB::transaction(function () use ($validated, $site, $request) {
            $apartments = Apartment::query()
                ->whereHas('buildingBlock', fn ($query) => $query->where('site_id', $site->id))
                ->when($validated['target'] === 'occupied', fn ($query) => $query->where('status', 'occupied'))
                ->get();

            $created = 0;

            foreach ($apartments as $apartment) {
                $due = Due::query()->firstOrCreate(
                    [
                        'apartment_id' => $apartment->id,
                        'period_year' => $validated['period_year'],
                        'period_month' => $validated['period_month'],
                    ],
                    [
                        'amount' => $validated['amount'],
                        'due_date' => $validated['due_date'],
                        'note' => $validated['note'] ?: $this->months()[(int) $validated['period_month']] . ' aidatı',
                    ]
                );

                if ($due->wasRecentlyCreated) {
                    $created++;
                }
            }

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'create_dues',
                'table_name' => 'dues',
                'ip_address' => $request->ip(),
                'description' => $this->months()[(int) $validated['period_month']] . ' ' . $validated['period_year'] . " dönemi için {$created} tahakkuk oluşturuldu.",
            ]);

            return $created;
        });

        return back()->with('status', "{$created} daire için aidat tahakkuku oluşturuldu. Daha önce oluşturulan kayıtlar atlandı.");
    }

    public function edit(Due $due): View
    {
        return view('dues.edit', [
            'due' => $due->load(['apartment.buildingBlock', 'apartment.activeResident']),
            'months' => $this->months(),
        ]);
    }

    public function update(Request $request, Due $due): RedirectResponse
    {
        $validated = $request->validate([
            'period_year' => ['required', 'integer', 'between:2020,2100'],
            'period_month' => ['required', 'integer', 'between:1,12'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'due_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $paid = (float) $due->payments()->sum('amount');

        if ((float) $validated['amount'] < $paid) {
            return back()
                ->withErrors(['amount' => 'Aidat tutarı, tahsil edilmiş toplamdan düşük olamaz.'])
                ->withInput();
        }

        $due->update($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_due',
            'table_name' => 'dues',
            'record_id' => $due->id,
            'ip_address' => $request->ip(),
            'description' => "{$due->apartment->number} aidat kaydı güncellendi.",
        ]);

        return redirect()->route('dues.index', [
            'year' => $due->period_year,
            'month' => $due->period_month,
        ])->with('status', 'Aidat kaydı güncellendi.');
    }

    public function destroy(Request $request, Due $due): RedirectResponse
    {
        if ($due->payments()->exists()) {
            return back()->withErrors(['delete' => 'Ödeme alınmış aidat silinemez. Önce ödeme kaydını silmelisiniz.']);
        }

        $periodYear = $due->period_year;
        $periodMonth = $due->period_month;
        $description = "{$due->apartment->number} {$this->months()[$periodMonth]} {$periodYear} aidatı silindi.";
        $due->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'delete_due',
            'table_name' => 'dues',
            'ip_address' => $request->ip(),
            'description' => $description,
        ]);

        return redirect()->route('dues.index', [
            'year' => $periodYear,
            'month' => $periodMonth,
        ])->with('status', 'Aidat kaydı silindi.');
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
