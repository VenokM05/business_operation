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

class ProjectOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_sync_team_and_staff_cannot_assign(): void
    {
        $manager = User::factory()->manager()->create();
        $staff = User::factory()->staff()->create();
        $project = Project::factory()->create();
        $this->actingAs($manager)->patch(route('projects.staff', $project), ['staff_ids' => [$staff->id]])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('project_user', ['project_id' => $project->id, 'user_id' => $staff->id]);
        $this->get(route('projects.show', $project))->assertOk()->assertSee('Save Team')->assertSee('Activity History');
        $this->patchJson(route('projects.staff', $project), ['staff_ids' => [99999]])->assertUnprocessable();
        $this->assertSame(1, $project->users()->count());
        $this->actingAs($staff)->patch(route('projects.staff', $project), ['staff_ids' => []])->assertForbidden();
        $this->actingAs($manager)->patch(route('projects.staff', $project), ['staff_ids' => []])->assertSessionHasNoErrors();
        $this->assertSame(0, $project->users()->count());
        $this->assertDatabaseCount('activity_logs', 2);
    }

    public function test_project_client_changes_sync_active_and_archived_requests(): void
    {
        $manager = User::factory()->manager()->create();
        $project = Project::factory()->create(['status' => 'planning']);
        $requests = ServiceRequest::factory()->count(2)->create(['project_id' => $project->id, 'client_id' => $project->client_id]);
        $requests->last()->delete();
        $client = Client::factory()->create();
        $this->actingAs($manager)->put(route('projects.update', $project), [
            'name' => $project->name, 'client_id' => $client->id, 'status' => 'active', 'priority' => 'high', 'currency' => 'USD', 'project_manager_id' => $manager->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame(2, ServiceRequest::withTrashed()->where('project_id', $project->id)->where('client_id', $client->id)->count());
        $this->assertDatabaseHas('activity_logs', ['action' => 'project.status_changed']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'project.manager_assigned']);
    }

    public function test_resource_archives_can_be_filtered_and_restored_without_losing_children(): void
    {
        $manager = User::factory()->manager()->create();
        $project = Project::factory()->create();
        $request = ServiceRequest::factory()->create(['project_id' => $project->id, 'client_id' => $project->client_id]);
        $this->actingAs($manager);
        foreach (['clients' => $project->client, 'projects' => $project, 'service-requests' => $request] as $resource => $record) {
            $this->delete(route($resource.'.destroy', $record))->assertRedirect(route($resource.'.index'));
            $this->assertSoftDeleted($record);
            $this->get(route($resource.'.index', ['archive' => 'archived']))->assertOk()->assertSee('Restore');
            $this->patch(route($resource.'.restore', $record))->assertRedirect(route($resource.'.show', $record));
            $this->assertNotSoftDeleted($record);
        }
        $this->assertDatabaseCount('projects', 1);
        $this->assertDatabaseCount('service_requests', 1);
        $this->assertDatabaseCount('activity_logs', 6);
    }

    public function test_long_audit_descriptions_are_preserved(): void
    {
        $manager = User::factory()->manager()->create();
        $project = Project::factory()->create(['name' => str_repeat('P', 255)]);
        $staff = User::factory()->count(5)->create(['name' => str_repeat('N', 100)]);
        $this->actingAs($manager)->patch(route('projects.staff', $project), ['staff_ids' => $staff->modelKeys()])->assertSessionHasNoErrors();
        $this->assertGreaterThan(255, strlen(ActivityLog::firstOrFail()->description));
    }

    public function test_demo_seed_counts_and_relationships_remain_valid(): void
    {
        $this->seed();
        $this->assertDatabaseCount('users', 5);
        $this->assertDatabaseCount('clients', 10);
        $this->assertDatabaseCount('projects', 10);
        $this->assertDatabaseCount('service_requests', 20);
        $this->assertDatabaseCount('tasks', 20);
        foreach (ServiceRequest::with('project')->get() as $request) {
            $this->assertSame($request->project->client_id, $request->client_id);
        }
        foreach (Task::all() as $task) {
            $this->assertNotNull($task->taskable);
        }
    }
}
