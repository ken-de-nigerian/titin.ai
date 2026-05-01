<?php

use App\Jobs\ProcessUserCvJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('uploads a valid CV and dispatches background processing', function () {
    Storage::fake('public');
    Queue::fake();

    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('user.cv.items.store'), [
            'resume' => UploadedFile::fake()->create('resume.pdf', 200, 'application/pdf'),
        ]);

    $response->assertCreated();
    $this->assertDatabaseCount('user_cvs', 1);
    Queue::assertPushed(ProcessUserCvJob::class);
});

it('rejects invalid CV mime type', function () {
    Storage::fake('public');
    Queue::fake();

    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('user.cv.items.store'), [
            'resume' => UploadedFile::fake()->create('resume.exe', 100, 'application/octet-stream'),
        ]);

    $response->assertSessionHasErrors('resume');
    $this->assertDatabaseCount('user_cvs', 0);
    Queue::assertNothingPushed();
});
