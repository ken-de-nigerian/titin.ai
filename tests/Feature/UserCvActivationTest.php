<?php

use App\Models\User;
use App\Models\UserCv;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps only one active CV per user after activation', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $first = UserCv::query()->create([
        'user_id' => $user->id,
        'path' => 'resumes/1/first.pdf',
        'client_original_name' => 'first.pdf',
        'original_name' => 'first_a1b2c3d4.pdf',
        'mime' => 'application/pdf',
        'size' => 1200,
        'status' => 'parsed',
        'is_active' => true,
    ]);

    $second = UserCv::query()->create([
        'user_id' => $user->id,
        'path' => 'resumes/1/second.pdf',
        'client_original_name' => 'second.pdf',
        'original_name' => 'second_e5f6g7h8.pdf',
        'mime' => 'application/pdf',
        'size' => 1800,
        'status' => 'parsed',
        'is_active' => false,
    ]);

    $this
        ->actingAs($user)
        ->post(route('user.cv.items.activate', ['cv' => $second->id]))
        ->assertOk();

    expect($first->fresh()?->is_active)->toBeFalse();
    expect($second->fresh()?->is_active)->toBeTrue();
});
