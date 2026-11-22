<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'membership_number',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'email',
        'phone',
        'address',
        'unit_floor_building',
        'house_street',
        'barangay',
        'city_municipality',
        'province',
        'zip_code',
        'latitude',
        'longitude',
        'birth_date',
        'membership_category_id',
        'membership_level_id',
        'status',
        'joined_at',
        'archived_at',
        'is_legacy_test_data',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'joined_at' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'archived_at' => 'datetime',
            'is_legacy_test_data' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MembershipCategory::class, 'membership_category_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(MembershipLevel::class, 'membership_level_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MembershipTransaction::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(MemberRequest::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(MemberDocument::class);
    }

    public function latestTransaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MembershipTransaction::class)->latestOfMany();
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->unit_floor_building,
            $this->house_street,
            $this->barangay,
            $this->city_municipality,
            $this->province,
            $this->zip_code,
        ]);

        return $parts ? implode(', ', $parts) : (string) ($this->address ?? '');
    }
}
