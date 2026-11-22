<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminProfile extends Model
{
    protected $fillable = [
        'user_id', 'first_name', 'middle_name', 'last_name', 'phone', 'birth_date', 'gender',
        'address', 'unit_floor_building', 'house_street', 'barangay', 'city_municipality',
        'province', 'zip_code', 'latitude', 'longitude',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([$this->first_name, $this->middle_name, $this->last_name])));
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
