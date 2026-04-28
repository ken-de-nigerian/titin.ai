<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('job_role')->nullable()->after('status');
            $table->string('interview_type')->nullable()->after('job_role');
            $table->string('resume_path')->nullable()->after('interview_type');
            $table->timestamp('onboarding_completed_at')->nullable()->after('resume_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'job_role',
                'interview_type',
                'resume_path',
                'onboarding_completed_at',
            ]);
        });
    }
};

