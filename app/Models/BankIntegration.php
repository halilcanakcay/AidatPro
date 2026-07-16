<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankIntegration extends Model
{
    protected $fillable = [
        'site_id',
        'provider',
        'environment',
        'customer_no',
        'account_no',
        'iban',
        'corporate_username',
        'corporate_password',
        'service_url',
        'sync_interval_minutes',
        'last_synced_at',
        'is_active',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'corporate_password' => 'encrypted',
            'last_synced_at' => 'datetime',
            'is_active' => 'boolean',
            'options' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }
}
