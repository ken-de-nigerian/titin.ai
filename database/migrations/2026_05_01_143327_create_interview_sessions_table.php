<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('interview_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('room_name', 120);
            $table->string('status', 24)->default('started');
            $table->string('job_role', 200)->nullable();
            $table->string('interview_mode', 32)->nullable();
            $table->string('interview_type', 64)->nullable();
            $table->unsignedTinyInteger('question_count')->default(6);
            $table->unsignedInteger('planned_duration_seconds')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->json('messages_json')->nullable();
            $table->json('feedback_json')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_sessions');
    }
};
