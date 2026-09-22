<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['comment', 'status_change', 'assignment'])->default('comment');
            $table->text('message')->nullable();
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('service_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_updates');
    }
};
