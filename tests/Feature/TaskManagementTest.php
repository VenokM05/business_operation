<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ServiceRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $extra = []): array
    {
        return [...['title' => 'Verify deployment', 'priority' => 'high', 'status' => 'to_do', 'due_date' => '2026-10-01'], ...$extra];
    }

    public function test_manager_can_create_tasks_under_both_parent_types_and_render_all_screens(): void
    {
        $manager = User::factory()->manager()->create();
        $project = Project::factory()->create();
        $request = ServiceRequest::factory()->create();
        $this->actingAs($manager);
        foreach (['project' => $project, 'request' => $request] as $type => $parent) {
            $this->post(route('tasks.store'), $this->payload(['parent_type' => $type, 'parent_id' => $parent->id]))->assertSessionHasNoErrors();
            $task = Task::latest('id')->firstOrFail();
            $this->assertSame($parent::class, $task->taskable_type);
            $this->assertSame($parent->id, $task->taskable_id);
            $this->get(route('tasks.show', $task))->assertOk();
            $this->get(route('tasks.edit', $task))->assertOk();
        }
        $this->get(route('tasks.index'))->assertOk();
        $this->get(route('tasks.create'))->assertOk();
        $this->assertDatabaseCount('activity_logs', 2);
    }

    public function test_staff_creation_is_parent_scoped_and_assigned_to_self(): void
    {
        $staff = User::factory()->staff()->create();
        $project = Project::factory()->create();
        $this->actingAs($staff);
        $payload = $this->payload(['parent_type' => 'project', 'parent_id' => $project->id]);
        $this->postJson(route('tasks.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('parent_id');
        $project->users()->attach($staff);
        $this->postJson(route('tasks.store'), [...$payload, 'assigned_to' => $staff->id])->assertUnprocessable();
        $this->post(route('tasks.store'), $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tasks', ['created_by' => $staff->id, 'assigned_to' => $staff->id]);
        $project->delete();
        $this->postJson(route('tasks.store'), $payload)->assertUnprocessable();
    }

    public function test_invalid_parent_type_ids_and_payloads_do_not_create_orphan_tasks(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        foreach ([['parent_type' => 'App\\Models\\User', 'parent_id' => 1], ['parent_type' => 'project', 'parent_id' => 9999], ['parent_type' => 'project', 'parent_id' => []]] as $parent) {
            $this->postJson(route('tasks.store'), $this->payload($parent))->assertUnprocessable();
        }
        $this->assertDatabaseCount('tasks', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_creator_cannot_modify_task_after_it_is_assigned_to_someone_else(): void
    {
        $staff = User::factory()->staff()->create();
        $other = User::factory()->staff()->create();
        $task = Task::factory()->for(Project::factory(), 'taskable')->create(['created_by' => $staff->id, 'assigned_to' => $other->id]);
        $this->actingAs($staff)->get(route('tasks.show', $task))->assertOk();
        $this->put(route('tasks.update', $task), $this->payload())->assertForbidden();
        $this->delete(route('tasks.destroy', $task))->assertForbidden();
        $this->actingAs($other)->put(route('tasks.update', $task), $this->payload(['status' => 'completed']))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'completed']);
        $this->putJson(route('tasks.update', $task), $this->payload(['assigned_to' => null, 'parent_id' => 2]))->assertUnprocessable();
        $this->assertSame($other->id, $task->fresh()->assigned_to);
    }

    public function test_assigned_staff_can_archive_but_only_manager_can_restore(): void
    {
        $staff = User::factory()->staff()->create();
        $task = Task::factory()->for(Project::factory(), 'taskable')->create(['assigned_to' => $staff->id]);
        $this->actingAs($staff)->delete(route('tasks.destroy', $task))->assertRedirect(route('tasks.index'));
        $this->assertSoftDeleted($task);
        $this->get(route('tasks.show', $task))->assertNotFound();
        $this->patch(route('tasks.restore', $task))->assertForbidden();
        $manager = User::factory()->manager()->create();
        $this->actingAs($manager)->get(route('tasks.index', ['archive' => 'archived']))->assertOk()->assertSee($task->title)->assertSee('Restore');
        $this->patch(route('tasks.restore', $task))->assertRedirect(route('tasks.show', $task));
        $this->assertNotSoftDeleted($task);
        $this->assertDatabaseCount('activity_logs', 2);
    }

    public function test_task_parent_names_are_not_exposed_without_parent_permission(): void
    {
        $staff = User::factory()->staff()->create();
        $project = Project::factory()->create(['name' => 'ConfidentialParentName']);
        $task = Task::factory()->for($project, 'taskable')->create(['assigned_to' => $staff->id]);
        $this->actingAs($staff)->get(route('tasks.index'))->assertOk()->assertDontSee('ConfidentialParentName');
        $this->get(route('tasks.show', $task))->assertOk()->assertSee('Parent unavailable')->assertDontSee('ConfidentialParentName');
        $project->delete();
        $this->get(route('tasks.show', $task))->assertOk();
    }
}
