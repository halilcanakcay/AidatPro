<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Due extends Model
{
    protected $fillable = [
        'apartment_id',
        'period_year',
        'period_month',
        'amount',
        'due_date',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentSourceLabel(): ?string
    {
        if ($this->payments->isEmpty()) {
            return null;
        }

        if ($this->payments->contains(fn (Payment $payment) => $payment->source_key === 'auto')) {
            return 'Otomatik entegrasyon';
        }

        if ($this->payments->contains(fn (Payment $payment) => $payment->source_key === 'integration_manual')) {
            return 'Manuel entegrasyon';
        }

        return 'Panelden işlendi';
    }

    public function paymentSourceBadgeClass(): string
    {
        return match ($this->paymentSourceLabel()) {
            'Otomatik entegrasyon' => 'bg-green-lt',
            'Manuel entegrasyon' => 'bg-blue-lt',
            'Panelden işlendi' => 'bg-secondary-lt',
            default => 'bg-secondary-lt',
        };
    }
}
