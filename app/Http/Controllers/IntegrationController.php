<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BankIntegration;
use App\Models\BankTransaction;
use App\Models\Due;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\Site;
use App\Services\VakifbankSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function vakifbank(Request $request): View
    {
        $sites = Site::query()->orderBy('name')->get();
        $site = Site::query()->find($request->query('site_id')) ?? $sites->firstOrFail();
        $tab = $request->query('tab', 'transactions');

        $integration = BankIntegration::query()->firstOrCreate(
            ['site_id' => $site->id, 'provider' => 'vakifbank'],
            [
                'environment' => 'test',
                'service_url' => 'https://vbtestservice.vakifbank.com.tr/HesapHareketleri.OnlineEkstre/SOnlineEkstreServis.svc?wsdl',
                'sync_interval_minutes' => 5,
            ],
        );

        $transactions = BankTransaction::query()
            ->with(['due.apartment.buildingBlock', 'due.apartment.activeResident', 'payment'])
            ->where('bank_integration_id', $integration->id)
            ->latest('transaction_date')
            ->paginate(10, ['*'], 'transactions_page')
            ->withQueryString();

        $successfulTransactions = BankTransaction::query()
            ->with(['due.apartment.activeResident', 'payment'])
            ->where('bank_integration_id', $integration->id)
            ->whereIn('status', ['matched', 'manual_matched'])
            ->latest('transaction_date')
            ->paginate(8, ['*'], 'successful_page')
            ->withQueryString();

        $failedTransactions = BankTransaction::query()
            ->with(['due.apartment.activeResident'])
            ->where('bank_integration_id', $integration->id)
            ->whereIn('status', ['unmatched', 'needs_review', 'failed'])
            ->latest('transaction_date')
            ->paginate(8, ['*'], 'failed_page')
            ->withQueryString();

        $dues = Due::query()
            ->with(['apartment.buildingBlock', 'apartment.activeResident', 'payments'])
            ->whereHas('apartment.buildingBlock', fn ($query) => $query->where('site_id', $site->id))
            ->latest('period_year')
            ->latest('period_month')
            ->get()
            ->filter(fn (Due $due) => $this->pendingAmount($due) > 0)
            ->sortBy(fn (Due $due) => $due->apartment->buildingBlock->name . $due->apartment->number)
            ->values();

        $nameWarnings = $this->nameWarnings($failedTransactions->getCollection());
        $descriptionPreview = $this->descriptionPreview($site, $request);

        return view('integrations.vakifbank', [
            'sites' => $sites,
            'site' => $site,
            'tab' => $tab,
            'integration' => $integration,
            'transactions' => $transactions,
            'successfulTransactions' => $successfulTransactions,
            'failedTransactions' => $failedTransactions,
            'dues' => $dues,
            'nameWarnings' => $nameWarnings,
            'descriptionPreview' => $descriptionPreview,
            'stats' => [
                'total' => BankTransaction::where('bank_integration_id', $integration->id)->count(),
                'matched' => BankTransaction::where('bank_integration_id', $integration->id)->whereIn('status', ['matched', 'manual_matched'])->count(),
                'failed' => BankTransaction::where('bank_integration_id', $integration->id)->whereIn('status', ['unmatched', 'needs_review', 'failed'])->count(),
                'last_synced_at' => $integration->last_synced_at,
            ],
        ]);
    }

    public function updateVakifbankSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_id' => ['required', 'exists:sites,id'],
            'environment' => ['required', 'in:test,production'],
            'customer_no' => ['required', 'string', 'max:20'],
            'account_no' => ['required', 'string', 'size:17'],
            'iban' => ['nullable', 'string', 'max:34'],
            'corporate_username' => ['required', 'string', 'max:80'],
            'corporate_password' => ['nullable', 'string', 'max:80'],
            'service_url' => ['required', 'url', 'max:255'],
            'sync_interval_minutes' => ['required', 'integer', 'between:1,60'],
            'description_template' => ['nullable', 'string', 'max:160'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $integration = BankIntegration::query()->firstOrCreate([
            'site_id' => $validated['site_id'],
            'provider' => 'vakifbank',
        ]);

        if (empty($validated['corporate_password'])) {
            unset($validated['corporate_password']);
        }

        $options = $integration->options ?? [];
        $options['description_template'] = $validated['description_template'] ?? null;
        unset($validated['description_template']);

        $integration->update($validated + [
            'provider' => 'vakifbank',
            'is_active' => $request->boolean('is_active'),
            'options' => $options,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_vakifbank_settings',
            'table_name' => 'bank_integrations',
            'record_id' => $integration->id,
            'ip_address' => $request->ip(),
            'description' => 'Vakıfbank entegrasyon ayarları güncellendi.',
        ]);

        return redirect()
            ->route('integrations.vakifbank', ['site_id' => $integration->site_id, 'tab' => 'settings'])
            ->with('status', 'Vakıfbank ayarları kaydedildi.');
    }

    public function syncVakifbank(Request $request, VakifbankSyncService $syncService): RedirectResponse
    {
        $validated = $request->validate([
            'site_id' => ['required', 'exists:sites,id'],
        ]);

        $integration = BankIntegration::query()
            ->where('site_id', $validated['site_id'])
            ->where('provider', 'vakifbank')
            ->firstOrFail();

        $result = $syncService->sync($integration);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'manual_vakifbank_sync',
            'table_name' => 'bank_integrations',
            'record_id' => $integration->id,
            'ip_address' => $request->ip(),
            'description' => 'Vakıfbank manuel hesap hareketi kontrolü çalıştırıldı.',
        ]);

        return redirect()
            ->route('integrations.vakifbank', ['site_id' => $integration->site_id, 'tab' => 'transactions'])
            ->with('status', $result['message'] . " Yeni hareket: {$result['new_count']}, otomatik işlenen: {$result['matched_count']}, manuel bekleyen: {$result['manual_count']}.");
    }

    public function approveVakifbankTransaction(Request $request, BankTransaction $bankTransaction): RedirectResponse
    {
        $validated = $request->validate([
            'due_id' => ['required', Rule::exists('dues', 'id')],
        ]);

        if (in_array($bankTransaction->status, ['matched', 'manual_matched'], true)) {
            return back()->withErrors(['transaction' => 'Bu banka hareketi zaten ödeme kaydına bağlanmış.']);
        }

        $due = Due::query()->with(['payments', 'apartment.buildingBlock', 'apartment.activeResident'])->findOrFail($validated['due_id']);
        $pending = $this->pendingAmount($due);

        if ((float) $bankTransaction->amount > $pending) {
            return back()->withErrors(['due_id' => 'Banka tutarı seçilen aidatın kalan borcundan büyük.']);
        }

        $payment = Payment::create([
            'due_id' => $due->id,
            'amount' => $bankTransaction->amount,
            'method' => 'bank',
            'receipt_no' => $this->bankReceiptNumber($bankTransaction),
            'paid_at' => $bankTransaction->transaction_date,
        ]);

        $bankTransaction->update([
            'status' => 'manual_matched',
            'matched_due_id' => $due->id,
            'matched_payment_id' => $payment->id,
            'match_reason' => 'Yönetici tarafından manuel eşleştirildi.',
            'failure_reason' => null,
            'processed_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'approve_bank_transaction',
            'table_name' => 'bank_transactions',
            'record_id' => $bankTransaction->id,
            'ip_address' => $request->ip(),
            'description' => "{$due->apartment->number} için Vakıfbank hareketi manuel ödeme yapıldı.",
        ]);

        return redirect()
            ->route('integrations.vakifbank', ['site_id' => $bankTransaction->site_id, 'tab' => 'failed'])
            ->with('status', 'Banka hareketi seçilen aidata işlendi.');
    }

    private function pendingAmount(Due $due): float
    {
        return max(0, (float) $due->amount - (float) $due->payments->sum('amount'));
    }

    private function nameWarnings($transactions): array
    {
        $residents = Resident::query()
            ->with('apartment.buildingBlock')
            ->where('is_active', true)
            ->get();

        $warnings = [];

        foreach ($transactions as $transaction) {
            $sender = $this->normalizeName((string) $transaction->sender_name);

            if ($sender === '') {
                continue;
            }

            $matches = $residents
                ->map(function (Resident $resident) use ($sender) {
                    $residentName = $this->normalizeName($resident->full_name);
                    similar_text($sender, $residentName, $score);

                    return [
                        'resident' => $resident,
                        'score' => $score,
                    ];
                })
                ->filter(fn (array $match) => $match['score'] >= 78)
                ->sortByDesc('score')
                ->take(3)
                ->values();

            if ($matches->isNotEmpty()) {
                $warnings[$transaction->id] = $matches->all();
            }
        }

        return $warnings;
    }

    private function normalizeName(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $value = str_replace(['ı', 'ğ', 'ü', 'ş', 'ö', 'ç'], ['i', 'g', 'u', 's', 'o', 'c'], $value);
        $value = preg_replace('/[^a-z0-9 ]/u', ' ', $value) ?? '';

        return trim((string) preg_replace('/\s+/', ' ', $value));
    }

    private function descriptionPreview(Site $site, Request $request): ?array
    {
        $description = trim((string) $request->query('preview_description', ''));

        if ($description === '') {
            return null;
        }

        $amount = $request->query('preview_amount') !== null && $request->query('preview_amount') !== ''
            ? (float) $request->query('preview_amount')
            : null;

        $normalizedDescription = $this->normalizeName($description);
        $compactDescription = $this->compactCode($description);
        $month = $this->findMonth($normalizedDescription);

        $dues = Due::query()
            ->with(['apartment.buildingBlock', 'apartment.activeResident', 'payments'])
            ->whereHas('apartment.buildingBlock', fn ($query) => $query->where('site_id', $site->id))
            ->get();

        $matches = $dues
            ->map(function (Due $due) use ($normalizedDescription, $compactDescription, $month, $amount) {
                $apartment = $due->apartment;
                $resident = $apartment->activeResident;
                $apartmentHit = str_contains($compactDescription, $this->compactCode($apartment->number));
                $monthHit = $month === null || (int) $due->period_month === $month;
                $nameHit = $resident ? $this->residentNameAppears($normalizedDescription, $resident->full_name) : false;
                $pending = $this->pendingAmount($due);
                $amountHit = $amount === null || abs($pending - $amount) < 0.01 || abs((float) $due->amount - $amount) < 0.01;

                $score = 0;
                $score += $apartmentHit ? 55 : 0;
                $score += $monthHit ? 20 : 0;
                $score += $nameHit ? 15 : 0;
                $score += $amountHit ? 10 : 0;

                return [
                    'due' => $due,
                    'score' => $score,
                    'apartment_hit' => $apartmentHit,
                    'month_hit' => $monthHit,
                    'name_hit' => $nameHit,
                    'amount_hit' => $amountHit,
                    'pending' => $pending,
                ];
            })
            ->filter(fn (array $match) => $match['apartment_hit'])
            ->sortByDesc('score')
            ->values();

        $best = $matches->first();

        if (! $best) {
            return [
                'status' => 'failed',
                'message' => 'Daire kodu bulunamadı. Bu açıklama otomatik işlenmez, manuel onaya düşer.',
                'matches' => [],
            ];
        }

        if (! $best['month_hit']) {
            return [
                'status' => 'warning',
                'message' => 'Daire bulundu ama dönem net değil. Sistem ödeme kaydı oluşturmaz, manuel onay ister.',
                'matches' => $matches->take(3),
            ];
        }

        if ($matches->where('score', $best['score'])->count() > 1) {
            return [
                'status' => 'warning',
                'message' => 'Birden fazla olası eşleşme var. Sistem otomatik işlem yapmaz, manuel onay ister.',
                'matches' => $matches->take(3),
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Daire ve dönem net yakalandı. Tutar da uygunsa otomatik ödeme kaydı oluşturulabilir.',
            'matches' => $matches->take(3),
        ];
    }

    private function compactCode(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', $this->normalizeName($value)) ?? '';
    }

    private function findMonth(string $description): ?int
    {
        foreach ($this->months() as $number => $name) {
            if (str_contains($description, $this->normalizeName($name)) || preg_match('/(^|\s)' . $number . '($|\s)/', $description)) {
                return $number;
            }
        }

        return null;
    }

    private function residentNameAppears(string $description, string $fullName): bool
    {
        $name = $this->normalizeName($fullName);

        if ($name !== '' && str_contains($description, $name)) {
            return true;
        }

        $parts = array_filter(explode(' ', $name), fn (string $part) => mb_strlen($part) >= 3);
        $hitCount = collect($parts)->filter(fn (string $part) => str_contains($description, $part))->count();

        return $hitCount >= min(2, count($parts));
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

    private function bankReceiptNumber(BankTransaction $transaction): string
    {
        return 'BNK-' . now()->format('Ymd') . '-' . $transaction->id;
    }
}
