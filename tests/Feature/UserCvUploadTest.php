<?php

use App\Jobs\ProcessUserCvJob;
use App\Models\User;
use App\Models\UserCv;
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

it('stores distinct original_name values when the same client filename is uploaded twice', function () {
    Storage::fake('public');
    Queue::fake();

    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $makeFile = fn (): UploadedFile => UploadedFile::fake()->create('resume.pdf', 200, 'application/pdf');

    $this->actingAs($user)->post(route('user.cv.items.store'), [
        'resume' => $makeFile(),
    ])->assertCreated();

    $this->actingAs($user)->post(route('user.cv.items.store'), [
        'resume' => $makeFile(),
    ])->assertCreated();

    $rows = UserCv::query()->orderBy('id')->get(['original_name', 'client_original_name']);

    expect($rows)->toHaveCount(2);
    expect($rows[0]->original_name)->not->toBe($rows[1]->original_name);
    expect($rows[0]->client_original_name)->toBe('resume.pdf');
    expect($rows[1]->client_original_name)->toBe('resume.pdf');
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
