<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_requests_are_rejected_with_envelope(): void
    {
        $this->getJson('/api/v1/clients')
            ->assertUnauthorized()
            ->assertJson(['success' => false, 'message' => 'Unauthenticated.']);
    }

    public function test_login_issues_a_usable_bearer_token(): void
    {
        $user = User::factory()->manager()->create();

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', 'manager')
            ->json('data.token');

        $this->assertNotEmpty($token);

        $this->withToken($token)->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_invalid_credentials_return_validation_envelope(): void
    {
        User::factory()->create(['email' => 'a@b.test', 'password' => bcrypt('secret123')]);

        $this->postJson('/api/v1/auth/login', ['email' => 'a@b.test', 'password' => 'wrong'])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('email');
    }

    public function test_manager_can_run_client_crud_and_delete_is_a_soft_archive(): void
    {
        Sanctum::actingAs(User::factory()->manager()->create());

        $created = $this->postJson('/api/v1/clients', [
            'company_name' => 'Acme API', 'status' => 'active', 'email' => 'ops@acme.test',
        ])->assertStatus(201)->assertJsonPath('success', true)->assertJsonPath('data.company_name', 'Acme API');

        $id = $created->json('data.id');

        $this->getJson('/api/v1/clients')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson("/api/v1/clients/{$id}")->assertOk()->assertJsonPath('data.company_name', 'Acme API');
        $this->putJson("/api/v1/clients/{$id}", ['company_name' => 'Acme Renamed', 'status' => 'inactive'])
            ->assertOk()->assertJsonPath('data.status', 'inactive');
        $this->deleteJson("/api/v1/clients/{$id}")->assertOk()->assertJsonPath('data', null);

        // Soft archive: the row survives with deleted_at set (not a hard delete).
        $this->assertSoftDeleted('clients', ['id' => $id]);
        $this->assertDatabaseHas('clients', ['id' => $id]);
    }

    public function test_validation_errors_use_the_error_envelope(): void
    {
        Sanctum::actingAs(User::factory()->manager()->create());

        $this->postJson('/api/v1/clients', ['status' => 'active'])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['success', 'message', 'errors' => ['company_name']]);
    }

    public function test_staff_cannot_create_and_only_sees_visible_records(): void
    {
        $staff = User::factory()->staff()->create();
        $hidden = Client::factory()->create();

        Sanctum::actingAs($staff);

        $this->postJson('/api/v1/clients', ['company_name' => 'Nope', 'status' => 'active'])->assertForbidden();
        $this->getJson('/api/v1/clients')->assertOk()->assertJsonPath('meta.total', 0);
        $this->getJson("/api/v1/clients/{$hidden->id}")->assertForbidden();
    }

    public function test_missing_record_returns_404_envelope(): void
    {
        Sanctum::actingAs(User::factory()->manager()->create());

        $this->getJson('/api/v1/clients/999999')
            ->assertNotFound()
            ->assertJson(['success' => false, 'message' => 'The requested resource was not found.']);
    }

    public function test_service_request_creation_enforces_workflow_via_api(): void
    {
        $manager = User::factory()->manager()->create();
        $client = Client::factory()->create();
        Sanctum::actingAs($manager);

        $this->postJson('/api/v1/service-requests', [
            'title' => 'Via API', 'client_id' => $client->id, 'category' => 'website', 'priority' => 'high',
        ])->assertStatus(201)
            ->assertJsonPath('data.status', 'new')
            ->assertJsonPath('data.request_number', ServiceRequest::first()->request_number);
    }
}
