<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            // Denormalized client_id (see PRD Section 14): kept in sync with the parent project's client.
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', ['website', 'software', 'technical_support', 'content', 'system_maintenance', 'other'])->default('other');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['new', 'assigned', 'in_progress', 'pending', 'resolved', 'closed', 'cancelled'])->default('new')->index();
            $table->date('due_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('priority');
            $table->index('category');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
