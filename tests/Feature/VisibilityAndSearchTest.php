<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Project;
use App\Models\ServiceRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisibilityAndSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_lists_dashboard_and_global_search_exclude_unrelated_records(): void
    {
        $staff = User::factory()->staff()->create();
        $visible = Client::factory()->create(['company_name' => 'Match Visible']);
        $hidden = Client::factory()->create(['company_name' => 'Match Confidential']);
        $project = Project::factory()->create(['client_id' => $visible->id, 'name' => 'Match Project', 'status' => 'active']);
        $project->users()->attach($staff);
        Project::factory()->create(['client_id' => $hidden->id, 'name' => 'Match Hidden Project', 'status' => 'active']);
        ServiceRequest::factory()->create(['client_id' => $visible->id, 'created_by' => $staff->id, 'title' => 'Match Request', 'status' => 'new']);
        ServiceRequest::factory()->create(['client_id' => $hidden->id, 'title' => 'Match Hidden Request', 'status' => 'new']);
        ActivityLog::record('client.created', $hidden, 'Sensitive audit content');
        $this->actingAs($staff)->get(route('clients.index'))->assertOk()->assertSee('Match Visible')->assertDontSee('Match Confidential');
        $this->get(route('projects.index'))->assertOk()->assertSee('Match Project')->assertDontSee('Match Hidden Project');
        $this->get(route('service-requests.index'))->assertOk()->assertSee('Match Request')->assertDontSee('Match Hidden Request');
        $this->get(route('service-requests.create'))->assertOk()->assertDontSee('Match Confidential')->assertDontSee('Match Hidden Project')->assertDontSee('name="assigned_to"', false);
        $this->get(route('search', ['q' => 'Match']))->assertOk()->assertSee('Match Visible')->assertSee('Match Request')->assertDontSee('Match Confidential')->assertDontSee('Match Hidden');
        $this->get(route('dashboard'))->assertOk()->assertDontSee('Sensitive audit content')->assertDontSee('Activity Logs')
            ->assertViewHas('stats', fn ($stats) => $stats['clients'] === 1 && $stats['active_projects'] === 1 && $stats['open_requests'] === 1);
    }

    public function test_related_detail_pages_do_not_leak_other_requests_projects_or_tasks(): void
    {
        $staff = User::factory()->staff()->create();
        $client = Client::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->id, 'project_manager_id' => $staff->id]);
        Project::factory()->create(['client_id' => $client->id, 'name' => 'HiddenSiblingProject']);
        ServiceRequest::factory()->create(['client_id' => $client->id, 'project_id' => $project->id, 'title' => 'HiddenSiblingRequest']);
        Task::factory()->for($project, 'taskable')->create(['title' => 'HiddenSiblingTask']);
        $this->actingAs($staff)->get(route('clients.show', $client))->assertOk()->assertDontSee('HiddenSiblingProject')->assertDontSee('HiddenSiblingRequest');
        $this->get(route('projects.show', $project))->assertOk()->assertDontSee('HiddenSiblingRequest')->assertDontSee('HiddenSiblingTask');
        $this->get(route('clients.index'))->assertViewHas('clients', fn ($clients) => $clients->first()->projects_count === 1 && $clients->first()->service_requests_count === 0);
    }

    public function test_staff_cannot_access_global_logs_but_managers_can_filter_them(): void
    {
        $staff = User::factory()->staff()->create();
        $manager = User::factory()->manager()->create();
        $client = Client::factory()->create();
        ActivityLog::record('client.created', $client, 'Target action', $manager->id);
        ActivityLog::record('client.updated', $client, 'Other action', $staff->id);
        $this->actingAs($staff)->get(route('activity-logs.index'))->assertForbidden();
        $this->actingAs($manager)->get(route('activity-logs.index', ['entity' => 'client', 'user_id' => $manager->id, 'action' => 'client.created', 'from' => today()->format('Y-m-d'), 'to' => today()->format('Y-m-d')]))
            ->assertOk()->assertSee('Target action')->assertDontSee('Other action')
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 1);
        $this->get(route('search'))->assertOk()->assertSee('Enter a search term');
        $this->get(route('search', ['q' => 'DefinitelyNoMatch']))->assertOk()->assertSee('No matches.');
    }

    public function test_combined_filters_and_pagination_preserve_query_parameters(): void
    {
        $manager = User::factory()->manager()->create();
        $client = Client::factory()->create();
        $attributes = ['client_id' => $client->id, 'title' => 'FilterTarget', 'priority' => 'high', 'status' => 'pending', 'assigned_to' => $manager->id, 'due_date' => '2026-10-10', 'category' => 'software'];
        ServiceRequest::factory()->count(12)->create($attributes);
        ServiceRequest::factory()->create([...$attributes, 'due_date' => '2026-10-11']);
        $filters = ['q' => 'FilterTarget', 'status' => 'pending', 'priority' => 'high', 'client_id' => $client->id, 'assigned_to' => $manager->id, 'category' => 'software', 'from' => '2026-10-10', 'to' => '2026-10-10'];
        $this->actingAs($manager)->get(route('service-requests.index', $filters))->assertOk()
            ->assertViewHas('requests', fn ($items) => $items->total() === 12 && $items->count() === 10 && str_contains($items->nextPageUrl(), 'priority=high'));
        $this->get(route('service-requests.index', $filters + ['page' => 2]))->assertOk()->assertViewHas('requests', fn ($items) => $items->count() === 2);
        $this->getJson(route('projects.index', ['from' => '2026-10-10', 'to' => '2026-10-01']))->assertUnprocessable();
        $this->getJson(route('clients.index', ['q' => ['bad']]))->assertUnprocessable();
        $this->getJson(route('service-requests.index', ['status' => 'unknown']))->assertUnprocessable();
        $this->getJson(route('search', ['q' => ['bad']]))->assertUnprocessable();
    }

    public function test_guests_must_authenticate_for_phase_two_pages(): void
    {
        foreach (['tasks.index', 'tasks.create', 'activity-logs.index', 'search'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }
}
