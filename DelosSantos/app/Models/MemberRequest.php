<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberRequest extends Model
{
    public const TYPE_REACTIVATION = 'reactivation';
    public const TYPE_CATEGORY_CHANGE = 'category_change';
    public const TYPE_PROFILE_CHANGE = 'profile_change';

    public const SOURCE_ONLINE = 'online';
    public const SOURCE_FRONT_DESK = 'frontdesk';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'member_id', 'submitted_by_user_id', 'request_type', 'source', 'status',
        'previous_values', 'proposed_values', 'consent_at', 'reviewed_by_user_id',
        'reviewed_at', 'reviewer_notes', 'email_reverification_hash',
        'email_reverification_expires_at', 'email_reverified_at',
    ];

    protected function casts(): array
    {
        return [
            'previous_values' => 'array',
            'proposed_values' => 'array',
            'consent_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'email_reverification_expires_at' => 'datetime',
            'email_reverified_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function submittedBy(): BelongsTo { return $this->belongsTo(User::class, 'submitted_by_user_id'); }
    public function reviewedBy(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by_user_id'); }
    public function documents(): HasMany { return $this->hasMany(MemberDocument::class); }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
