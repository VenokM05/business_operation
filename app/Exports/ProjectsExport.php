<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Project::query()->with(['client', 'manager'])->orderBy('name');
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Client', 'Manager', 'Status', 'Priority', 'Start Date', 'End Date', 'Budget', 'Currency'];
    }

    /** @param  Project  $project */
    public function map($project): array
    {
        return [
            $project->id,
            $project->name,
            $project->client?->company_name,
            $project->manager?->name,
            $project->status?->label(),
            $project->priority?->label(),
            $project->start_date?->toDateString(),
            $project->end_date?->toDateString(),
            $project->budget,
            $project->currency,
        ];
    }
}
