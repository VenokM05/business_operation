<?php

namespace App\Http\Requests;

use App\Enums\ClientStatus;
use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        $client = $this->route('client');

        return $client
            ? $this->user()->can('update', $client)
            : $this->user()->can('create', Client::class);
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'industry' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(ClientStatus::class)],
        ];
    }
}
