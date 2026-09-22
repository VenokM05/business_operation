<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Client::query()->orderBy('company_name');
    }

    public function headings(): array
    {
        return ['ID', 'Company', 'Contact Person', 'Email', 'Phone', 'Industry', 'Status', 'Created At'];
    }

    /** @param  Client  $client */
    public function map($client): array
    {
        return [
            $client->id,
            $client->company_name,
            $client->contact_person,
            $client->email,
            $client->phone,
            $client->industry,
            $client->status?->label(),
            $client->created_at?->toDateString(),
        ];
    }
}
