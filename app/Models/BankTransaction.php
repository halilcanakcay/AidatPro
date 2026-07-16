<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    protected $fillable = [
        'bank_integration_id',
        'site_id',
        'provider',
        'bank_transaction_id',
        'operation_no',
        'transaction_date',
        'amount',
        'direction',
        'sender_name',
        'sender_iban',
        'description',
        'status',
        'matched_due_id',
        'matched_payment_id',
        'match_reason',
        'failure_reason',
        'raw_payload',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'amount' => 'decimal:2',
            'raw_payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(BankIntegration::class, 'bank_integration_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function due(): BelongsTo
    {
        return $this->belongsTo(Due::class, 'matched_due_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'matched_payment_id');
    }
}
