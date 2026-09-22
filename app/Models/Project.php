<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'client_id',
        'description',
        'project_manager_id',
        'start_date',
        'end_date',
        'priority',
        'status',
        'budget',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'priority' => Priority::class,
            'status' => ProjectStatus::class,
            'budget' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (self $project) {
            if ($project->wasChanged('client_id')) {
                $project->serviceRequests()->withTrashed()->update(['client_id' => $project->client_id]);
            }
        });
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->when($user->isStaff(), fn (Builder $q) => $q->where(
            fn (Builder $q) => $q->where('project_manager_id', $user->id)
                ->orWhereHas('users', fn (Builder $q) => $q->whereKey($user->id))
        ));
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    /** Staff assigned to this project. */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    protected function isOverdue(): Attribute
    {
        return Attribute::get(fn (): bool => $this->end_date !== null
            && $this->end_date->isPast()
            && ! in_array($this->status, [ProjectStatus::Completed, ProjectStatus::Cancelled], true));
    }

    /*
    |--------------------------------------------------------------------------
    | Query scopes
    |--------------------------------------------------------------------------
    */

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
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

    public function scopeForClient(Builder $query, ?int $clientId): Builder
    {
        return $clientId ? $query->where('client_id', $clientId) : $query;
    }
}
