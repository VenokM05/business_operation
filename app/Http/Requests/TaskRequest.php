<?php

namespace App\Http\Requests;

use App\Enums\Priority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\ServiceRequest;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $this->user()->can($task ? 'update' : 'create', $task ?? Task::class);
    }

    public function rules(): array
    {
        $editing = $this->route('task') !== null;

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', Rule::enum(Priority::class)],
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'due_date' => ['nullable', 'date_format:Y-m-d'],
            'assigned_to' => $this->user()->isStaff() ? ['prohibited'] : ['nullable', 'integer', Rule::exists('users', 'id')],
            'parent_type' => $editing ? ['prohibited'] : ['required', Rule::in(['project', 'request'])],
            'parent_id' => $editing ? ['prohibited'] : ['required', 'integer', 'min:1'],
            'created_by' => ['prohibited'],
            'taskable_type' => ['prohibited'],
            'taskable_id' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty() || $this->route('task')) {
                return;
            }
            $model = $this->input('parent_type') === 'project' ? Project::class : ServiceRequest::class;
            if (! $model::visibleTo($this->user())->whereKey($this->integer('parent_id'))->exists()) {
                $validator->errors()->add('parent_id', 'Select an available parent that you have permission to view.');
            }
        });
    }
}
