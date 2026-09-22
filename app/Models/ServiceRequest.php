<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\RequestCategory;
use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_number',
        'client_id',
        'project_id',
        'title',
        'description',
        'category',
        'priority',
        'assigned_to',
        'status',
        'due_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'category' => RequestCategory::class,
            'priority' => Priority::class,
            'status' => RequestStatus::class,
            'due_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        // Auto-generate a human-friendly request number (SR-0001, SR-0002, ...).
        static::creating(function (self $request) {
            if (blank($request->request_number)) {
                $next = (int) (static::withTrashed()->max('id') ?? 0) + 1;
                $request->request_number = sprintf('SR-%04d', $next);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(RequestUpdate::class)->latest('created_at')->orderByDesc('id');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /*
    |--------------------------------------------------------------------------
    | Workflow helpers (PRD Section 8)
    |--------------------------------------------------------------------------
    */

    public function canMoveTo(RequestStatus $target): bool
    {
        return $this->status->canTransitionTo($target);
    }

    /*
    |--------------------------------------------------------------------------
    | Query scopes (PRD Section 11)
    |--------------------------------------------------------------------------
    */

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->when($user->isStaff(), fn (Builder $q) => $q->where(
            fn (Builder $q) => $q->where('assigned_to', $user->id)->orWhere('created_by', $user->id)
        ));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('request_number', 'like', "%{$term}%")
                ->orWhere('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopePriority(Builder $query, ?string $priority): Builder
    {
        return $priority ? $query->where('priority', $priority) : $query;
    }

    public function scopeAssignedTo(Builder $query, ?int $userId): Builder
    {
        return $userId ? $query->where('assigned_to', $userId) : $query;
    }

    public function scopeForClient(Builder $query, ?int $clientId): Builder
    {
        return $clientId ? $query->where('client_id', $clientId) : $query;
    }

    /** Not yet resolved/closed/cancelled. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            RequestStatus::Resolved->value,
            RequestStatus::Closed->value,
            RequestStatus::Cancelled->value,
        ]);
    }
}
