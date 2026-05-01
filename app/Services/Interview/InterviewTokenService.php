<?php

declare(strict_types=1);

namespace App\Services\Interview;

use App\DTOs\Interview\IssueInterviewTokenData;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class InterviewTokenService
{
    /**
     * @return array{token: string, room: string}
     *
     * @throws RequestException|ConnectionException
     */
    public function issueForUser(User $user, IssueInterviewTokenData $data): array
    {
        $tokenServerUrl = (string) config('services.livekit.token_server_url');
        $sharedSecret = (string) config('services.livekit.internal_secret');

        if ($tokenServerUrl === '' || $sharedSecret === '') {
            throw new RuntimeException('LiveKit token server integration is not configured.');
        }

        $jobRole = trim((string) ($data->jobRole ?? $user->job_role ?? 'Software Engineer'));
        $interviewType = trim((string) ($data->interviewType ?? $user->interview_type ?? config('settings.interview.default_type', 'mixed')));

        $response = Http::acceptJson()
            ->asJson()
            ->withHeaders([
                'X-Internal-Token' => $sharedSecret,
            ])
            ->connectTimeout(5)
            ->timeout(10)
            ->retry(2, 200)
            ->post(rtrim($tokenServerUrl, '/').'/internal/issue-token', [
                'user_id' => (int) $user->id,
                'email' => (string) $user->email,
                'name' => (string) $user->name,
                'job_role' => $jobRole !== '' ? $jobRole : 'Software Engineer',
                'interview_type' => $interviewType !== '' ? $interviewType : (string) config('settings.interview.default_type', 'mixed'),
                'concise_feedback' => (bool) $user->prefers_concise_feedback,
            ])
            ->throw();

        /** @var mixed $payload */
        $payload = $response->json();
        if (! is_array($payload) || ! isset($payload['token'], $payload['room'])) {
            throw new RuntimeException('Token server returned an invalid response.');
        }

        return [
            'token' => (string) $payload['token'],
            'room' => (string) $payload['room'],
        ];
    }
}
