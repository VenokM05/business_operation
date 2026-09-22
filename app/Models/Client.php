<?php

namespace App\Models;

use App\Enums\ClientStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'industry',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ClientStatus::class,
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query scopes (server-side filtering, PRD Section 11)
    |--------------------------------------------------------------------------
    */

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->when($user->isStaff(), fn (Builder $q) => $q->where(
            fn (Builder $q) => $q->whereHas('projects', fn (Builder $q) => $q->visibleTo($user))
                ->orWhereHas('serviceRequests', fn (Builder $q) => $q->visibleTo($user))
        ));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('company_name', 'like', "%{$term}%")
                ->orWhere('contact_person', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeIndustry(Builder $query, ?string $industry): Builder
    {
        return $industry ? $query->where('industry', $industry) : $query;
    }
}
