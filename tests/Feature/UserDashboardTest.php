<?php

use App\Models\InterviewSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

it('includes human readable ended_at for recent sessions on the dashboard', function () {
    Carbon::setTestNow(Carbon::parse('2026-05-02 14:00:00', 'UTC'));

    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $endedAt = now()->subHours(2);

    InterviewSession::query()->create([
        'user_id' => $user->id,
        'room_name' => 'room-dash-1',
        'status' => 'completed',
        'job_role' => 'Backend Engineer',
        'interview_mode' => 'simulation',
        'interview_type' => 'technical',
        'question_count' => 6,
        'planned_duration_seconds' => 900,
        'duration_seconds' => 600,
        'feedback_json' => ['overall_score' => 8.2],
        'started_at' => now()->subHours(3),
        'ended_at' => $endedAt,
    ]);

    $expectedHuman = $endedAt->timezone((string) config('app.timezone', 'UTC'))->diffForHumans();

    $this->actingAs($user)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $assert) => $assert
            ->component('User/Dashboard')
            ->has('recentSessions', 1)
            ->where('recentSessions.0.ended_at_human', $expectedHuman)
            ->has('scoreTrajectory.points', 1)
            ->where('scoreTrajectory.points.0.score', 8.2)
            ->where('scoreTrajectory.trend', null));

    Carbon::setTestNow();
});

it('aggregates score trajectory across completed sessions', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    InterviewSession::query()->create([
        'user_id' => $user->id,
        'room_name' => 'room-traj-1',
        'status' => 'completed',
        'job_role' => 'Engineer',
        'interview_mode' => 'simulation',
        'interview_type' => 'technical',
        'question_count' => 6,
        'planned_duration_seconds' => 900,
        'duration_seconds' => 300,
        'feedback_json' => ['overall_score' => 6.0],
        'started_at' => now()->subDays(3),
        'ended_at' => now()->subDays(3),
    ]);

    InterviewSession::query()->create([
        'user_id' => $user->id,
        'room_name' => 'room-traj-2',
        'status' => 'completed',
        'job_role' => 'Engineer',
        'interview_mode' => 'simulation',
        'interview_type' => 'behavioral_interview',
        'question_count' => 6,
        'planned_duration_seconds' => 900,
        'duration_seconds' => 400,
        'feedback_json' => ['overall_score' => 8.0],
        'started_at' => now()->subDays(2),
        'ended_at' => now()->subDays(2),
    ]);

    InterviewSession::query()->create([
        'user_id' => $user->id,
        'room_name' => 'room-traj-3',
        'status' => 'completed',
        'job_role' => 'Engineer',
        'interview_mode' => 'simulation',
        'interview_type' => 'behavioral',
        'question_count' => 6,
        'planned_duration_seconds' => 900,
        'duration_seconds' => 500,
        'feedback_json' => ['overall_score' => 7.0],
        'started_at' => now()->subDay(),
        'ended_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $assert) => $assert
            ->component('User/Dashboard')
            ->has('scoreTrajectory.points', 3)
            ->where('scoreTrajectory.points.0.score', 6)
            ->where('scoreTrajectory.points.1.score', 8)
            ->where('scoreTrajectory.points.2.score', 7)
            ->where('scoreTrajectory.trend.direction', 'up')
            ->where('scoreTrajectory.trend.label', '+17%'));
});

it('includes every completed session in score trajectory, not a fixed window', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    for ($i = 1; $i <= 8; $i++) {
        InterviewSession::query()->create([
            'user_id' => $user->id,
            'room_name' => "room-traj-bulk-{$i}",
            'status' => 'completed',
            'interview_type' => 'technical',
            'question_count' => 6,
            'planned_duration_seconds' => 900,
            'duration_seconds' => 60,
            'feedback_json' => ['overall_score' => 5 + ($i * 0.1)],
            'started_at' => now()->subDays(30 - $i),
            'ended_at' => now()->subDays(30 - $i),
        ]);
    }

    $this->actingAs($user)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $assert) => $assert
            ->component('User/Dashboard')
            ->has('scoreTrajectory.points', 8));
});
