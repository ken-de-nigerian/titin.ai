<?php

use App\Jobs\ProcessUserCvJob;
use App\Models\User;
use App\Models\UserCv;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('allows CV upload during onboarding', function () {
    Storage::fake('public');
    Queue::fake();

    $user = User::factory()->create([
        'onboarding_completed_at' => null,
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('user.onboarding.cv.items.store'), [
            'resume' => UploadedFile::fake()->create('resume.pdf', 200, 'application/pdf'),
        ]);

    $response->assertCreated();
    $this->assertDatabaseCount('user_cvs', 1);
    Queue::assertPushed(ProcessUserCvJob::class);
});

it('shows only the latest uploaded CV on onboarding', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => null,
    ]);

    UserCv::query()->create([
        'user_id' => $user->id,
        'path' => 'resumes/1/first.pdf',
        'client_original_name' => 'first.pdf',
        'original_name' => 'first_a1b2c3d4.pdf',
        'mime' => 'application/pdf',
        'size' => 1200,
        'status' => 'parsed',
        'is_active' => false,
    ]);

    UserCv::query()->create([
        'user_id' => $user->id,
        'path' => 'resumes/1/second.pdf',
        'client_original_name' => 'second.pdf',
        'original_name' => 'second_e5f6g7h8.pdf',
        'mime' => 'application/pdf',
        'size' => 1800,
        'status' => 'parsed',
        'is_active' => true,
    ]);

    $this
        ->actingAs($user)
        ->get(route('user.onboarding.show'))
        ->assertOk()
        ->assertSeeText('second_e5f6g7h8.pdf')
        ->assertDontSeeText('first_a1b2c3d4.pdf');
});
