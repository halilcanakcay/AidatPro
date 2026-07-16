<?php

namespace App\Services;

use App\Models\BankIntegration;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class VakifbankSyncService
{
    /**
     * The SOAP request will be wired here when live Vakifbank credentials are available.
     *
     * @return array{checked_at:\Illuminate\Support\Carbon,new_count:int,matched_count:int,manual_count:int,message:string}
     */
    public function sync(BankIntegration $integration): array
    {
        $this->validateReady($integration);

        $checkedAt = now();
        $integration->update(['last_synced_at' => $checkedAt]);

        return [
            'checked_at' => $checkedAt,
            'new_count' => 0,
            'matched_count' => 0,
            'manual_count' => 0,
            'message' => 'Banka kontrolü çalıştırıldı. Canlı Vakıfbank SOAP bağlantısı aktif edildiğinde gelen yeni hareketler bu kontrol sırasında alınacaktır.',
        ];
    }

    public function shouldRun(BankIntegration $integration): bool
    {
        if (! $integration->is_active) {
            return false;
        }

        if (! $integration->last_synced_at) {
            return true;
        }

        return $integration->last_synced_at->lte(now()->subMinutes((int) $integration->sync_interval_minutes));
    }

    private function validateReady(BankIntegration $integration): void
    {
        $validator = Validator::make($integration->only([
            'customer_no',
            'account_no',
            'corporate_username',
            'corporate_password',
            'service_url',
        ]), [
            'customer_no' => ['required'],
            'account_no' => ['required', 'size:17'],
            'corporate_username' => ['required'],
            'corporate_password' => ['required'],
            'service_url' => ['required', 'url'],
        ], [
            'customer_no.required' => 'Müşteri no girilmeden banka kontrolü başlatılamaz.',
            'account_no.required' => 'Hesap no girilmeden banka kontrolü başlatılamaz.',
            'account_no.size' => 'Hesap no 17 haneli olmalıdır.',
            'corporate_username.required' => 'Kurum kullanıcı adı girilmeden banka kontrolü başlatılamaz.',
            'corporate_password.required' => 'Şifre girilmeden banka kontrolü başlatılamaz.',
            'service_url.required' => 'Servis adresi girilmeden banka kontrolü başlatılamaz.',
            'service_url.url' => 'Servis adresi geçerli bir URL olmalıdır.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
