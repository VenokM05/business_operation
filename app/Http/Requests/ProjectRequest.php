<?php

namespace App\Http\Requests;

use App\Enums\Priority;
use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project
            ? $this->user()->can('update', $project)
            : $this->user()->can('create', Project::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')->whereNull('deleted_at')],
            'description' => ['nullable', 'string', 'max:5000'],
            'project_manager_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'priority' => ['required', Rule::enum(Priority::class)],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'currency' => ['required', 'string', 'size:3'],
        ];
    }
}
