<?php

namespace Database\Seeders;

use App\Enums\Priority;
use App\Enums\ClientStatus;
use App\Enums\ProjectStatus;
use App\Enums\RequestCategory;
use App\Enums\RequestStatus;
use App\Enums\RequestUpdateType;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Project;
use App\Models\RequestUpdate;
use App\Models\ServiceRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Users (Admin / Manager / Staff) ----
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@boms.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);

        $manager = User::factory()->create([
            'name' => 'Maria Garcia',
            'email' => 'manager@boms.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Manager,
        ]);

        $staff = collect([
            ['name' => 'John Smith', 'email' => 'staff@boms.test'],
            ['name' => 'Alex Chen', 'email' => 'alex@boms.test'],
            ['name' => 'Priya Nair', 'email' => 'priya@boms.test'],
        ])->map(fn ($u) => User::factory()->create([
            ...$u,
            'password' => Hash::make('password'),
            'role' => UserRole::Staff,
        ]));

        // ---- Clients ----
        $clients = Client::factory()->count(10)->create()->shuffle();

        // ---- Projects (each tied to a client, managed by the manager) ----
        $projects = collect();
        foreach ($clients as $client) {
            $project = Project::factory()->create([
                'client_id' => $client->id,
                'project_manager_id' => $manager->id,
                'status' => $client->status === ClientStatus::Inactive ? ProjectStatus::OnHold : fake()->randomElement(ProjectStatus::cases()),
            ]);

            // Assign 1-2 staff to the project.
            $project->users()->attach($staff->random(min(2, $staff->count()))->pluck('id')->unique()->all());

            $projects->push($project);
        }

        // ---- Service requests (20), linked to projects + denormalized client ----
        $allUsers = (clone $staff)->push($manager)->push($admin);
        for ($i = 0; $i < 20; $i++) {
            $project = $projects->random();

            $request = ServiceRequest::factory()->create([
                'project_id' => $project->id,
                // Invariant: client_id must match the parent project's client.
                'client_id' => $project->client_id,
                'created_by' => $manager->id,
                'assigned_to' => $allUsers->random()->id,
            ]);

            // A timeline entry for each request.
            RequestUpdate::create([
                'service_request_id' => $request->id,
                'user_id' => $manager->id,
                'type' => RequestUpdateType::Comment,
                'message' => fake()->sentence(8),
                'created_at' => now()->subDays(rand(1, 20)),
            ]);
        }

        // ---- Tasks (20), polymorphic to a project or a request ----
        $requests = ServiceRequest::inRandomOrder()->limit(10)->get();

        for ($i = 0; $i < 20; $i++) {
            $parent = fake()->boolean() ? $projects->random() : $requests->random();

            Task::factory()->create([
                'taskable_type' => $parent->getMorphClass(),
                'taskable_id' => $parent->getKey(),
                'assigned_to' => $staff->random()->id,
                'created_by' => $manager->id,
                'status' => fake()->randomElement(TaskStatus::cases()),
            ]);
        }

        // ---- Activity logs (audit trail) ----
        foreach ($clients->take(6) as $client) {
            ActivityLog::record('client.created', $client, "Admin User created client {$client->company_name}", $admin->id);
        }
        foreach ($projects->take(6) as $project) {
            ActivityLog::record('project.created', $project, "Maria Garcia created project {$project->name}", $manager->id);
        }
        foreach (ServiceRequest::inRandomOrder()->limit(8)->get() as $request) {
            ActivityLog::record('request.created', $request, "Created request {$request->request_number}", $manager->id);
        }

        $this->command?->info('BOMS demo data seeded: 5 users, 10 clients, 10 projects, 20 requests, 20 tasks, activity logs.');
    }
}
