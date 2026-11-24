<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ARCHIVED = 'archived';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_LOW = 'low';

    public const PRIORITIES = [self::PRIORITY_HIGH, self::PRIORITY_MEDIUM, self::PRIORITY_LOW];

    protected $fillable = [
        'member_id',
        'created_by_user_id',
        'task_title',
        'priority',
        'description',
        'status',
        'deadline',
        'published_at',
        'archived_at',
        'completed',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
            'completed_at' => 'datetime',
            'deadline' => 'datetime',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function extensionRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaskExtensionRequest::class);
    }

    public function effectiveStatus(): string
    {
        if ($this->archived_at !== null || $this->status === self::STATUS_ARCHIVED) {
            return self::STATUS_ARCHIVED;
        }

        if ($this->completed || $this->status === self::STATUS_COMPLETED) {
            return self::STATUS_COMPLETED;
        }

        if ($this->deadline?->isPast()) {
            return 'overdue';
        }

        return $this->status ?: self::STATUS_ASSIGNED;
    }
}
