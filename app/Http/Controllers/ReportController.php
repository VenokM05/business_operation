<?php

namespace App\Http\Controllers;

use App\Exports\ClientsExport;
use App\Exports\ProjectsExport;
use App\Exports\ServiceRequestsExport;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /** report key => [export class, download slug]. */
    private const EXPORTS = [
        'clients' => [ClientsExport::class, 'clients'],
        'projects' => [ProjectsExport::class, 'projects'],
        'service-requests' => [ServiceRequestsExport::class, 'service-requests'],
    ];

    public function __construct(private readonly ReportService $reports) {}

    public function index(Request $request)
    {
        $this->authorize('viewReports');

        $user = $request->user();

        return view('reports.index', [
            'clients' => $this->reports->clientReport($user),
            'projects' => $this->reports->projectReport($user),
            'requests' => $this->reports->serviceRequestReport($user),
        ]);
    }

    public function export(Request $request, string $report, string $format)
    {
        $this->authorize('exportReports');

        abort_unless(isset(self::EXPORTS[$report]), 404);
        abort_unless(in_array($format, ['csv', 'xlsx'], true), 404);

        [$exporter, $slug] = self::EXPORTS[$report];
        $filename = "boms-{$slug}-".now()->format('Ymd-His').".{$format}";
        $writerType = $format === 'xlsx' ? \Maatwebsite\Excel\Excel::XLSX : \Maatwebsite\Excel\Excel::CSV;

        return Excel::download(new $exporter, $filename, $writerType);
    }
}
