<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\BankIntegration;
use App\Services\VakifbankSyncService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bank:sync-vakifbank {--site_id=}', function (VakifbankSyncService $syncService) {
    $query = BankIntegration::query()
        ->where('provider', 'vakifbank')
        ->where('is_active', true);

    if ($siteId = $this->option('site_id')) {
        $query->where('site_id', $siteId);
    }

    $checked = 0;

    $query->with('site')->get()->each(function (BankIntegration $integration) use ($syncService, &$checked) {
        if (! $syncService->shouldRun($integration)) {
            return;
        }

        try {
            $result = $syncService->sync($integration);
            $checked++;
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->warn($integration->site?->name . ': ' . $exception->validator->errors()->first());

            return;
        }

        $this->info($integration->site?->name . ': ' . $result['message']);
    });

    if ($checked === 0) {
        $this->info('Kontrol zamanı gelen aktif Vakıfbank entegrasyonu bulunamadı.');
    }
})->purpose('Aktif Vakıfbank hesap hareketi entegrasyonlarını kontrol eder');

Schedule::command('bank:sync-vakifbank')->everyMinute()->withoutOverlapping();
