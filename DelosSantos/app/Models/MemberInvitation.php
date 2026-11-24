<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MemberInvitation extends Model
{
    protected $fillable = [
        'member_id',
        'user_id',
        'token_hash',
        'expires_at',
        'consumed_at',
        'created_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function isUsable(): bool
    {
        return $this->consumed_at === null && $this->expires_at->isFuture();
    }

    public static function issueFor(Member $member, User $user, ?User $admin = null): array
    {
        $token = Str::random(64);

        $invitation = static::create([
            'member_id' => $member->id,
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHours(24),
            'created_by_admin_id' => $admin?->id,
        ]);

        return [$invitation, $token];
    }

    public static function findUsableByToken(string $token): ?self
    {
        $invitation = static::with(['member', 'user'])
            ->where('token_hash', hash('sha256', $token))
            ->first();

        return $invitation?->isUsable() ? $invitation : null;
    }
}
