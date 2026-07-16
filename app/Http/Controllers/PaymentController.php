<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BuildingBlock;
use App\Models\Due;
use App\Models\Payment;
use App\Models\Site;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $sites = Site::query()->orderBy('name')->get();
        $site = Site::query()->find($request->query('site_id')) ?? $sites->firstOrFail();
        $blocks = BuildingBlock::query()->where('site_id', $site->id)->orderBy('name')->get();
        $blockId = $blocks->firstWhere('id', $request->integer('block_id'))?->id;
        $periodYear = (int) $request->query('year', 2026);
        $periodMonth = $request->filled('month') ? max(1, min(12, (int) $request->query('month'))) : null;
        $method = (string) $request->query('method', '');
        $source = (string) $request->query('source', '');
        $search = trim((string) $request->query('search', ''));

        $payments = Payment::query()
            ->with(['bankTransaction', 'due.apartment.buildingBlock', 'due.apartment.activeResident'])
            ->whereHas('due.apartment.buildingBlock', fn ($query) => $query->where('site_id', $site->id))
            ->when($blockId, fn ($query) => $query->whereHas('due.apartment', fn ($apartment) => $apartment->where('building_block_id', $blockId)))
            ->whereHas('due', function ($query) use ($periodYear, $periodMonth) {
                $query->where('period_year', $periodYear)
                    ->when($periodMonth, fn ($query) => $query->where('period_month', $periodMonth));
            })
            ->when($method !== '', fn ($query) => $query->where('method', $method))
            ->when($source === 'auto', fn ($query) => $query->whereHas('bankTransaction', fn ($bank) => $bank->where('status', 'matched')))
            ->when($source === 'integration_manual', fn ($query) => $query->whereHas('bankTransaction', fn ($bank) => $bank->where('status', 'manual_matched')))
            ->when($source === 'panel', fn ($query) => $query->whereDoesntHave('bankTransaction'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('receipt_no', 'like', "%{$search}%")
                        ->orWhereHas('due.apartment', fn ($apartment) => $apartment->where('number', 'like', "%{$search}%"))
                        ->orWhereHas('due.apartment.activeResident', fn ($resident) => $resident->where('full_name', 'like', "%{$search}%"));
                });
            })
            ->latest('paid_at')
            ->paginate(12)
            ->withQueryString();

        return view('payments.index', [
            'site' => $site,
            'sites' => $sites,
            'blocks' => $blocks,
            'blockId' => $blockId,
            'payments' => $payments,
            'months' => $this->months(),
            'periodYear' => $periodYear,
            'periodMonth' => $periodMonth,
            'method' => $method,
            'source' => $source,
            'search' => $search,
        ]);
    }

    public function create(Request $request): View
    {
        $sites = Site::query()->orderBy('name')->get();
        $site = Site::query()->find($request->query('site_id')) ?? $sites->firstOrFail();
        $blocks = BuildingBlock::query()->where('site_id', $site->id)->orderBy('name')->get();
        $blockId = $blocks->firstWhere('id', $request->integer('block_id'))?->id;
        $search = trim((string) $request->query('search', ''));

        $dues = Due::query()
            ->with(['apartment.buildingBlock', 'apartment.activeResident', 'payments'])
            ->whereHas('apartment.buildingBlock', fn ($query) => $query->where('site_id', $site->id))
            ->when($blockId, fn ($query) => $query->whereHas('apartment', fn ($apartment) => $apartment->where('building_block_id', $blockId)))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('period_year', 'like', "%{$search}%")
                        ->orWhere('period_month', 'like', "%{$search}%")
                        ->orWhereHas('apartment', fn ($apartment) => $apartment->where('number', 'like', "%{$search}%"))
                        ->orWhereHas('apartment.buildingBlock', fn ($block) => $block->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('apartment.activeResident', fn ($resident) => $resident->where('full_name', 'like', "%{$search}%"));
                });
            })
            ->latest('period_year')
            ->latest('period_month')
            ->get()
            ->filter(fn (Due $due) => $this->pendingAmount($due) > 0)
            ->sortBy(fn (Due $due) => $due->apartment->buildingBlock->name . $due->apartment->number)
            ->values();

        $selectedDue = $dues->firstWhere('id', (int) $request->query('due_id')) ?? $dues->first();

        return view('payments.create', compact('site', 'sites', 'blocks', 'blockId', 'dues', 'selectedDue', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'due_id' => ['required', 'exists:dues,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:bank,eft,card,cash'],
            'paid_at' => ['required', 'date'],
            'receipt_no' => ['nullable', 'string', 'max:40', Rule::unique('payments', 'receipt_no')],
        ]);

        $due = Due::query()->with(['apartment.buildingBlock', 'payments'])->findOrFail($validated['due_id']);
        $pending = $this->pendingAmount($due);

        if ((float) $validated['amount'] > $pending) {
            return back()
                ->withErrors(['amount' => 'Ödeme tutarı kalan borçtan büyük olamaz.'])
                ->withInput();
        }

        $payment = Payment::create([
            'due_id' => $due->id,
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'paid_at' => $validated['paid_at'],
            'receipt_no' => $validated['receipt_no'] ?: $this->receiptNumber($due->id),
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'create_payment',
            'table_name' => 'payments',
            'record_id' => $payment->id,
            'ip_address' => $request->ip(),
            'description' => "{$due->apartment->number} için ödeme kaydedildi.",
        ]);

        return redirect()->route('payments.receipt', $payment)->with('status', 'Ödeme kaydedildi. Makbuz hazır.');
    }

    public function edit(Payment $payment): View
    {
        $site = Site::query()->firstOrFail();

        return view('payments.edit', [
            'payment' => $payment->load(['due.apartment.buildingBlock', 'due.apartment.activeResident', 'due.payments']),
            'dues' => Due::query()
                ->with(['apartment.buildingBlock', 'apartment.activeResident', 'payments'])
                ->whereHas('apartment.buildingBlock', fn ($query) => $query->where('site_id', $site->id))
                ->latest('period_year')
                ->latest('period_month')
                ->get(),
        ]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'due_id' => ['required', 'exists:dues,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:bank,eft,card,cash'],
            'paid_at' => ['required', 'date'],
            'receipt_no' => ['required', 'string', 'max:40', Rule::unique('payments', 'receipt_no')->ignore($payment->id)],
        ]);

        $due = Due::query()->with('payments')->findOrFail($validated['due_id']);
        $otherPaid = (float) $due->payments()->whereKeyNot($payment->id)->sum('amount');
        $pendingWithCurrent = max(0, (float) $due->amount - $otherPaid);

        if ((float) $validated['amount'] > $pendingWithCurrent) {
            return back()
                ->withErrors(['amount' => 'Ödeme tutarı kalan borçtan büyük olamaz.'])
                ->withInput();
        }

        $payment->update($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_payment',
            'table_name' => 'payments',
            'record_id' => $payment->id,
            'ip_address' => $request->ip(),
            'description' => "{$payment->receipt_no} makbuzlu ödeme güncellendi.",
        ]);

        return redirect()->route('payments.index')->with('status', 'Ödeme güncellendi.');
    }

    public function destroy(Request $request, Payment $payment): RedirectResponse
    {
        $receiptNo = $payment->receipt_no;
        $payment->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'delete_payment',
            'table_name' => 'payments',
            'ip_address' => $request->ip(),
            'description' => "{$receiptNo} makbuzlu ödeme silindi.",
        ]);

        return redirect()->route('payments.index')->with('status', 'Ödeme silindi.');
    }

    public function receipt(Payment $payment): View
    {
        return view('payments.receipt', [
            'payment' => $payment->load(['bankTransaction', 'due.apartment.buildingBlock', 'due.apartment.activeResident']),
            'site' => Site::query()->firstOrFail(),
            'whatsappMessage' => $this->receiptMessage($payment->load(['due.apartment.activeResident'])),
        ]);
    }

    public function receiptPdf(Payment $payment): Response
    {
        $payment->load(['bankTransaction', 'due.apartment.buildingBlock', 'due.apartment.activeResident']);
        $html = view('payments.receipt-pdf', [
            'payment' => $payment,
            'site' => Site::query()->firstOrFail(),
        ])->render();

        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $payment->receipt_no . '.pdf"',
        ]);
    }

    private function pendingAmount(Due $due): float
    {
        return max(0, (float) $due->amount - (float) $due->payments->sum('amount'));
    }

    private function receiptNumber(int $dueId): string
    {
        return 'MAK-' . now()->format('YmdHis') . '-' . $dueId;
    }

    private function receiptMessage(Payment $payment): string
    {
        $resident = $payment->due->apartment->activeResident;
        $amount = number_format((float) $payment->amount, 2, ',', '.');

        return trim("Sayın {$resident?->full_name},\n{$payment->receipt_no} numaralı {$amount} TL ödeme makbuzunuz oluşturulmuştur.\nDaire: {$payment->due->apartment->number}\nTarih: {$payment->paid_at->format('d.m.Y H:i')}");
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
