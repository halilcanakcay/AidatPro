<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    protected $fillable = ['due_id', 'amount', 'method', 'receipt_no', 'paid_at'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function due(): BelongsTo
    {
        return $this->belongsTo(Due::class);
    }

    public function bankTransaction(): HasOne
    {
        return $this->hasOne(BankTransaction::class, 'matched_payment_id');
    }

    public function getSourceKeyAttribute(): string
    {
        return match ($this->bankTransaction?->status) {
            'matched' => 'auto',
            'manual_matched' => 'integration_manual',
            default => 'panel',
        };
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source_key) {
            'auto' => 'Otomatik entegrasyon',
            'integration_manual' => 'Manuel entegrasyon',
            default => 'Panelden işlendi',
        };
    }

    public function getSourceBadgeClassAttribute(): string
    {
        return match ($this->source_key) {
            'auto' => 'bg-green-lt',
            'integration_manual' => 'bg-blue-lt',
            default => 'bg-secondary-lt',
        };
    }
}
