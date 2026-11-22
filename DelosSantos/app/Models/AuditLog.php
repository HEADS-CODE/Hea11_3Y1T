<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'details',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return ['details' => 'array', 'created_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $auditLog): void {
            $auditLog->details = static::redact((array) $auditLog->details);
        });

        static::updating(function (): void {
            throw new LogicException('Audit logs are append-only.');
        });

        static::deleting(function (): void {
            throw new LogicException('Audit logs are append-only.');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function record(string $action, array $details = [], ?Model $auditable = null, ?User $user = null): self
    {
        return static::create([
            'user_id' => $user?->getKey() ?? auth()->id(),
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'details' => static::redact($details),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private static function redact(array $details): array
    {
        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'token',
            'access_token',
            'remember_token',
            'secret',
            'file',
            'file_contents',
            'content',
        ];

        $redacted = [];

        foreach ($details as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            $containsSensitiveWord = str_contains($normalizedKey, 'password')
                || str_contains($normalizedKey, 'token')
                || str_contains($normalizedKey, 'secret')
                || str_contains($normalizedKey, 'file_content');

            if (in_array($normalizedKey, $sensitiveKeys, true) || $containsSensitiveWord) {
                $redacted[$key] = '[REDACTED]';
                continue;
            }

            $redacted[$key] = is_array($value) ? static::redact($value) : $value;
        }

        return $redacted;
    }
}
