<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assignStaff', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'staff_ids' => ['sometimes', 'array', 'max:100'],
            'staff_ids.*' => ['integer', 'distinct', Rule::exists('users', 'id')],
        ];
    }
}
