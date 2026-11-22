<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberDocument extends Model
{
    protected $fillable = [
        'member_request_id', 'member_id', 'uploaded_by_user_id', 'document_type',
        'original_name', 'storage_path', 'mime_type', 'extension', 'size_bytes',
        'privacy_consented_at',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'privacy_consented_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo { return $this->belongsTo(MemberRequest::class, 'member_request_id'); }
    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function uploadedBy(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by_user_id'); }
}
