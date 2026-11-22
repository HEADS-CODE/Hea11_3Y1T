<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailVerificationToken extends Model
{
    protected $fillable = ['user_id', 'token_hash', 'expires_at', 'consumed_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function issueFor(User $user): string
    {
        $token = Str::random(64);

        static::updateOrCreate(
            ['user_id' => $user->id],
            [
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addHours(24),
                'consumed_at' => null,
            ],
        );

        return $token;
    }
}
