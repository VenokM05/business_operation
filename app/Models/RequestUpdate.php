<?php

namespace App\Models;

use App\Enums\RequestUpdateType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestUpdate extends Model
{
    use HasFactory;

    /**
     * Timeline entries are immutable; only created_at is managed.
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'service_request_id',
        'user_id',
        'type',
        'message',
        'old_status',
        'new_status',
    ];

    protected function casts(): array
    {
        return [
            'type' => RequestUpdateType::class,
            'created_at' => 'datetime',
        ];
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
