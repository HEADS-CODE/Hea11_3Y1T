<?php

namespace App\Models;

use App\Notifications\RoleScopedResetPassword;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    public const ROLE_MEMBER = 'member';
    public const ROLE_ADMIN = 'admin';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PENDING = 'pending';
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_REJECTED = 'rejected';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class);
    }

    public function memberInvitations(): HasMany
    {
        return $this->hasMany(MemberInvitation::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isMember(): bool
    {
        return $this->role === self::ROLE_MEMBER;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isBlocked(): bool
    {
        return in_array($this->status, [self::STATUS_DISABLED, self::STATUS_REJECTED], true)
            || ($this->member && $this->member->archived_at !== null);
    }

    public function isInactiveMember(): bool
    {
        return $this->isMember() && $this->member?->status === 'inactive' && $this->member->archived_at === null;
    }

    public function needsEmailVerification(): bool
    {
        return $this->email_verified_at === null;
    }

    public function isAwaitingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING
            || ($this->member && $this->member->status === 'pending');
    }

    public function canAccessPortal(): bool
    {
        return $this->isActive()
            && ! $this->needsEmailVerification()
            && ! $this->isAwaitingApproval()
            && ! $this->isBlocked()
            && ! $this->isInactiveMember();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new RoleScopedResetPassword($token, (string) $this->role));
    }
}
