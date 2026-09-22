<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'priority',
        'status',
        'due_date',
        'created_by',
        'taskable_type',
        'taskable_id',
    ];

    protected function casts(): array
    {
        return [
            'priority' => Priority::class,
            'status' => TaskStatus::class,
            'due_date' => 'date',
        ];
    }

    /** Polymorphic parent: a Project or a ServiceRequest. */
    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCompleted(): bool
    {
        return $this->status === TaskStatus::Completed;
    }

    /*
    |--------------------------------------------------------------------------
    | Query scopes
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
        return $query->when(filled($term), fn (Builder $q) => $q->where(
            fn (Builder $q) => $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
        ));
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', '!=', TaskStatus::Completed->value);
    }

    public function scopeAssignedTo(Builder $query, ?int $userId): Builder
    {
        return $userId ? $query->where('assigned_to', $userId) : $query;
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }
}
