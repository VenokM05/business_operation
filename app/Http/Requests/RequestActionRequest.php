<?php

namespace App\Http\Requests;

use App\Enums\RequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = match (true) {
            $this->routeIs('service-requests.assign') => 'assignStaff',
            $this->routeIs('service-requests.status') => 'changeStatus',
            default => 'update',
        };

        return $this->user()->can($ability, $this->route('service_request'));
    }

    public function rules(): array
    {
        return match (true) {
            $this->routeIs('service-requests.assign') => [
                'assigned_to' => ['present', 'nullable', 'integer', Rule::exists('users', 'id')],
            ],
            $this->routeIs('service-requests.status') => [
                'status' => ['required', Rule::enum(RequestStatus::class)],
            ],
            default => ['message' => ['required', 'string', 'max:2000']],
        };
    }
}
