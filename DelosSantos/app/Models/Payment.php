<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Payment extends Model
{
    protected $fillable = [
        'membership_transaction_id', 'amount', 'payer_name', 'payer_account_number', 'payer_address',
        'payer_latitude', 'payer_longitude', 'payment_reference', 'payment_method', 'status', 'paid_at', 'recorded_by_user_id',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'payer_latitude' => 'decimal:7', 'payer_longitude' => 'decimal:7', 'paid_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (Payment $payment): void {
            $transaction = MembershipTransaction::with('level')->find($payment->membership_transaction_id);

            if ($transaction && number_format((float) $payment->amount, 2, '.', '')
                !== number_format((float) $transaction->level->fee, 2, '.', '')) {
                throw ValidationException::withMessages([
                    'amount' => 'The payment amount must equal the selected membership level fee.',
                ]);
            }
        });
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(MembershipTransaction::class, 'membership_transaction_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
