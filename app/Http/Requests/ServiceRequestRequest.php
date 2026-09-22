<?php

namespace App\Http\Requests;

use App\Enums\Priority;
use App\Enums\RequestCategory;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $request = $this->route('service_request');

        return $request
            ? $this->user()->can('update', $request)
            : $this->user()->can('create', ServiceRequest::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category' => ['required', Rule::enum(RequestCategory::class)],
            'priority' => ['required', Rule::enum(Priority::class)],
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')->whereNull('deleted_at')],
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')->whereNull('deleted_at')],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'due_date' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        // The chosen project must belong to the chosen client (PRD Section 14 invariant).
        $validator->after(function (Validator $v) {
            if ($this->filled('project_id')) {
                $belongs = \App\Models\Project::whereKey($this->integer('project_id'))
                    ->where('client_id', $this->integer('client_id'))
                    ->exists();

                if (! $belongs) {
                    $v->errors()->add('project_id', 'The selected project does not belong to this client.');
                }
            }
        });
    }
}
