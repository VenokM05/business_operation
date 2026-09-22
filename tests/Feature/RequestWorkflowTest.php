<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Project;
use App\Models\RequestUpdate;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\RequestWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_assignment_starts_workflow_and_creates_both_timeline_entries(): void
    {
        $manager = User::factory()->manager()->create();
        $staff = User::factory()->staff()->create();
        $request = ServiceRequest::factory()->create(['status' => 'new']);
        $this->actingAs($manager)->patch(route('service-requests.assign', $request), ['assigned_to' => $staff->id])->assertSessionHasNoErrors();
        $this->assertSame(RequestStatus::Assigned, $request->fresh()->status);
        $this->assertSame($staff->id, $request->fresh()->assigned_to);
        $this->assertDatabaseCount('request_updates', 2);
        $this->assertDatabaseHas('request_updates', ['old_status' => 'new', 'new_status' => 'assigned', 'user_id' => $manager->id]);
        $this->assertDatabaseCount('activity_logs', 2);
        $this->actingAs($manager)->patch(route('service-requests.assign', $request), ['assigned_to' => $staff->id])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('request_updates', 2);
    }

    public static function transitions(): array
    {
        return [
            ['assigned', 'in_progress', true], ['in_progress', 'pending', true],
            ['pending', 'in_progress', true], ['in_progress', 'resolved', true],
            ['resolved', 'closed', true], ['new', 'cancelled', true],
            ['new', 'closed', false], ['assigned', 'resolved', false],
            ['closed', 'in_progress', false], ['cancelled', 'assigned', false],
        ];
    }

    #[DataProvider('transitions')]
    public function test_transition_matrix_is_enforced(string $from, string $to, bool $allowed): void
    {
        $staff = User::factory()->staff()->create();
        $request = ServiceRequest::factory()->create(['status' => $from, 'assigned_to' => $staff->id]);
        $response = $this->actingAs($staff)->patch(route('service-requests.status', $request), ['status' => $to]);
        if ($allowed) {
            $response->assertSessionHasNoErrors();
            $this->assertSame($to, $request->fresh()->status->value);
            $this->assertDatabaseCount('request_updates', 1);
            $this->assertDatabaseCount('activity_logs', 1);
        } else {
            $response->assertSessionHasErrors('status');
            $this->assertSame($from, $request->fresh()->status->value);
            $this->assertDatabaseCount('request_updates', 0);
            $this->assertDatabaseCount('activity_logs', 0);
        }
    }

    public function test_unrelated_staff_cannot_read_modify_comment_or_assign_a_request(): void
    {
        $staff = User::factory()->staff()->create();
        $request = ServiceRequest::factory()->create(['status' => 'assigned']);
        $this->actingAs($staff)->get(route('service-requests.show', $request))->assertForbidden();
        $this->patch(route('service-requests.status', $request), ['status' => 'in_progress'])->assertForbidden();
        $this->post(route('service-requests.comments', $request), ['message' => 'Hidden'])->assertForbidden();
        $this->patch(route('service-requests.assign', $request), ['assigned_to' => $staff->id])->assertForbidden();
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_creator_can_comment_but_staff_cannot_assign_or_inject_a_status(): void
    {
        $staff = User::factory()->staff()->create();
        $request = ServiceRequest::factory()->create(['created_by' => $staff->id, 'status' => 'new']);
        $this->actingAs($staff)->post(route('service-requests.comments', $request), ['message' => '<script>alert(1)</script>'])->assertSessionHasNoErrors();
        $this->get(route('service-requests.show', $request))->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->post(route('service-requests.comments', $request), ['message' => '  '])->assertSessionHasErrors('message');
        $this->patch(route('service-requests.assign', $request), ['assigned_to' => $staff->id])->assertForbidden();
        $payload = ['title' => 'Changed', 'category' => 'other', 'priority' => 'low'];
        $this->put(route('service-requests.update', $request), $payload + ['status' => 'closed'])->assertSessionHasErrors('status');
        $this->put(route('service-requests.update', $request), $payload)->assertSessionHasNoErrors();
        $this->assertSame(RequestStatus::New, $request->fresh()->status);
    }

    public function test_active_work_requires_an_assignee_and_terminal_requests_cannot_be_reassigned(): void
    {
        $manager = User::factory()->manager()->create();
        $staff = User::factory()->staff()->create();
        $request = ServiceRequest::factory()->create(['status' => 'new']);
        $this->actingAs($manager)->patch(route('service-requests.status', $request), ['status' => 'assigned'])->assertSessionHasErrors('status');
        $request->update(['status' => 'in_progress', 'assigned_to' => $staff->id]);
        $this->patch(route('service-requests.assign', $request), ['assigned_to' => null])->assertSessionHasErrors('assigned_to');
        $request->update(['status' => 'closed']);
        $this->patch(route('service-requests.assign', $request), ['assigned_to' => $manager->id])->assertSessionHasErrors('assigned_to');
    }

    public function test_create_with_assignment_uses_workflow_and_staff_create_is_scoped(): void
    {
        $manager = User::factory()->manager()->create();
        $staff = User::factory()->staff()->create();
        $project = Project::factory()->create();
        $payload = ['title' => 'New request', 'client_id' => $project->client_id, 'project_id' => $project->id, 'category' => 'other', 'priority' => 'medium'];
        $this->actingAs($manager)->post(route('service-requests.store'), $payload + ['assigned_to' => $staff->id])->assertSessionHasNoErrors();
        $created = ServiceRequest::firstOrFail();
        $this->assertSame(RequestStatus::Assigned, $created->status);
        $this->assertDatabaseCount('request_updates', 2);
        $this->actingAs($staff)->post(route('service-requests.store'), $payload)->assertSessionHasErrors('project_id');
        $project->users()->attach($staff);
        $this->post(route('service-requests.store'), $payload + ['assigned_to' => $manager->id])->assertSessionHasErrors('assigned_to');
        $this->post(route('service-requests.store'), $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('service_requests', ['created_by' => $staff->id, 'assigned_to' => null, 'status' => 'new']);
        $otherClient = Client::factory()->create();
        $this->actingAs($manager)->post(route('service-requests.store'), [...$payload, 'client_id' => $otherClient->id])->assertSessionHasErrors('project_id');
    }

    public function test_audit_failure_rolls_back_status_and_timeline(): void
    {
        $manager = User::factory()->manager()->create();
        $request = ServiceRequest::factory()->create(['status' => 'assigned', 'assigned_to' => $manager->id]);
        ActivityLog::creating(function () {
            throw new \RuntimeException('Audit unavailable');
        });
        try {
            app(RequestWorkflowService::class)->changeStatus($request, RequestStatus::InProgress, $manager);
            $this->fail('Expected audit failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Audit unavailable', $exception->getMessage());
        } finally {
            ActivityLog::flushEventListeners();
        }
        $this->assertSame(RequestStatus::Assigned, $request->fresh()->status);
        $this->assertDatabaseCount('request_updates', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_stale_model_cannot_overwrite_a_terminal_status(): void
    {
        $manager = User::factory()->manager()->create();
        $request = ServiceRequest::factory()->create(['status' => 'assigned', 'assigned_to' => $manager->id]);
        ServiceRequest::whereKey($request->id)->update(['status' => 'closed']);
        try {
            app(RequestWorkflowService::class)->changeStatus($request, RequestStatus::InProgress, $manager);
            $this->fail('Expected transition rejection.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }
        $this->assertSame(RequestStatus::Closed, $request->fresh()->status);
    }

    public function test_timeline_is_paginated_and_stable_for_identical_timestamps(): void
    {
        $staff = User::factory()->staff()->create();
        $request = ServiceRequest::factory()->create(['created_by' => $staff->id]);
        for ($i = 0; $i < 17; $i++) {
            RequestUpdate::create(['service_request_id' => $request->id, 'user_id' => $staff->id, 'type' => 'comment', 'message' => 'Note '.$i]);
        }
        $this->actingAs($staff)->get(route('service-requests.show', $request))->assertOk()
            ->assertViewHas('updates', fn ($updates) => $updates->total() === 17 && $updates->count() === 15 && $updates->first()->message === 'Note 16');
    }
}
