<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('updates interview duration minutes with other interview preferences', function () {
    $user = User::factory()->create([
        'job_role' => 'Engineer',
        'interview_type' => 'mixed',
        'seniority_level' => 'mid_level',
        'prefers_concise_feedback' => false,
        'interview_duration_minutes' => null,
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('user.profile.interview-preferences.update'), [
            'job_role' => 'Backend Engineer',
            'interview_type' => 'technical',
            'seniority_level' => 'senior',
            'prefers_concise_feedback' => true,
            'interview_duration_minutes' => 45,
        ])
        ->assertRedirect();

    $user->refresh();
    expect($user->job_role)->toBe('Backend Engineer')
        ->and($user->interview_type)->toBe('technical')
        ->and($user->seniority_level)->toBe('senior')
        ->and($user->prefers_concise_feedback)->toBeTrue()
        ->and($user->interview_duration_minutes)->toBe(45);
});

it('rejects interview duration outside configured bounds', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('user.profile.interview-preferences.update'), [
            'job_role' => 'Engineer',
            'interview_type' => 'mixed',
            'seniority_level' => 'mid_level',
            'prefers_concise_feedback' => false,
            'interview_duration_minutes' => 2,
        ])
        ->assertSessionHasErrors('interview_duration_minutes');
});
