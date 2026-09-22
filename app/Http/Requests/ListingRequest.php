<?php

namespace App\Http\Requests;

use App\Enums\ClientStatus;
use App\Enums\Priority;
use App\Enums\ProjectStatus;
use App\Enums\RequestCategory;
use App\Enums\RequestStatus;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $status = match (true) {
            $this->routeIs('clients.*') => ClientStatus::class,
            $this->routeIs('projects.*') => ProjectStatus::class,
            $this->routeIs('tasks.*') => TaskStatus::class,
            default => RequestStatus::class,
        };

        return [
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum($status)],
            'priority' => ['nullable', Rule::enum(Priority::class)],
            'category' => ['nullable', Rule::enum(RequestCategory::class)],
            'industry' => ['nullable', 'string', 'max:255'],
            'client_id' => ['nullable', 'integer', 'min:1'],
            'assigned_to' => ['nullable', 'integer', 'min:1'],
            'user_id' => ['nullable', 'integer', 'min:1'],
            'entity' => ['nullable', Rule::in(['client', 'project', 'request', 'task'])],
            'action' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', ...($this->filled('from') ? ['after_or_equal:from'] : [])],
            'archive' => ['nullable', Rule::in($this->user()->isStaff() ? ['active'] : ['active', 'archived'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
