<?php

namespace App\Exports;

use App\Models\ServiceRequest;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ServiceRequestsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return ServiceRequest::query()
            ->with(['client', 'assignee'])
            ->orderBy('request_number');
    }

    public function headings(): array
    {
        return ['Request', 'Title', 'Client', 'Status', 'Priority', 'Category', 'Assignee', 'Due Date'];
    }

    /** @param  ServiceRequest  $request */
    public function map($request): array
    {
        return [
            $request->request_number,
            $request->title,
            $request->client?->company_name,
            $request->status?->label(),
            $request->priority?->label(),
            $request->category?->label(),
            $request->assignee?->name,
            $request->due_date?->toDateString(),
        ];
    }
}
