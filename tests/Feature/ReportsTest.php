<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_view_reports(): void
    {
        $this->actingAs(User::factory()->staff()->create())
            ->get('/reports')
            ->assertForbidden();
    }

    public function test_manager_views_report_aggregates(): void
    {
        $manager = User::factory()->manager()->create();
        Client::factory()->create(['status' => 'active']);
        Client::factory()->create(['status' => 'inactive'])->delete();
        $project = Project::factory()->create(['status' => 'active']);
        ServiceRequest::factory()->create(['project_id' => $project->id, 'client_id' => $project->client_id, 'status' => 'new', 'priority' => 'high', 'category' => 'website']);

        $this->actingAs($manager)->get('/reports')->assertOk()
            ->assertSee('Client Report')
            ->assertSee('Project Report')
            ->assertSee('Service Request Report')
            ->assertSee('By staff');
    }

    public function test_csv_and_xlsx_exports_download(): void
    {
        $manager = User::factory()->manager()->create();
        Client::factory()->create(['company_name' => 'Export Co', 'status' => 'active']);
        $this->actingAs($manager);

        $csv = $this->get('/reports/export/clients/csv');
        $csv->assertOk();
        $disposition = $csv->headers->get('content-disposition', '');
        $this->assertStringContainsString('attachment', $disposition);
        $this->assertStringContainsString('boms-clients', $disposition);
        $this->assertStringContainsString('.csv', $disposition);

        $this->get('/reports/export/service-requests/xlsx')->assertOk();
    }

    public function test_unknown_report_or_format_is_rejected(): void
    {
        $this->actingAs(User::factory()->manager()->create());
        $this->get('/reports/export/ghost/csv')->assertNotFound();
        $this->get('/reports/export/clients/pdf')->assertNotFound();
    }

    public function test_staff_cannot_export(): void
    {
        $this->actingAs(User::factory()->staff()->create())
            ->get('/reports/export/clients/csv')
            ->assertForbidden();
    }
}
