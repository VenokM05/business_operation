<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    /** Audit entries are immutable; only created_at is managed. */
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Polymorphic audited entity. */
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Record an audit entry.
     */
    public static function record(
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        ?int $userId = null,
    ): self {
        return static::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'entity_type' => $subject?->getMorphClass(),
            'entity_id' => $subject?->getKey(),
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
