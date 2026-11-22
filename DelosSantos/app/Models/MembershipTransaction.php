<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MembershipTransaction extends Model
{
    public const TYPE_APPLICATION = 'application';
    public const TYPE_RENEWAL = 'renewal';
    public const TYPE_LEVEL_CHANGE = 'level_change';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CANCELLED = 'cancelled';
    public const CHANNEL_CARD = 'card';
    public const CHANNEL_BANK_TRANSFER = 'bank_transfer';
    public const CHANNEL_GCASH = 'gcash';
    public const CHANNEL_MAYA = 'maya';
    public const CHANNEL_FRONT_DESK = 'front_desk';

    public const ONLINE_CHANNELS = [
        self::CHANNEL_CARD,
        self::CHANNEL_BANK_TRANSFER,
        self::CHANNEL_GCASH,
        self::CHANNEL_MAYA,
    ];

    protected $fillable = [
        'member_id', 'membership_category_id', 'membership_level_id', 'reference_number', 'type', 'status', 'payment_channel', 'amount',
        'starts_at', 'ends_at', 'created_by_user_id', 'approved_by_user_id', 'approved_at', 'notes', 'cancel_reason',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'starts_at' => 'date', 'ends_at' => 'date', 'approved_at' => 'datetime'];
    }

    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function category(): BelongsTo { return $this->belongsTo(MembershipCategory::class, 'membership_category_id'); }
    public function level(): BelongsTo { return $this->belongsTo(MembershipLevel::class, 'membership_level_id'); }
    public function payment(): HasOne { return $this->hasOne(Payment::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by_user_id'); }

    public function effectiveStatus(): string
    {
        if ($this->status === self::STATUS_APPROVED && $this->ends_at?->isPast()) {
            return 'expired';
        }

        return $this->status;
    }

    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PAID
            && $this->payment
            && number_format((float) $this->payment->amount, 2, '.', '')
                === number_format((float) $this->level->fee, 2, '.', '');
    }
}
