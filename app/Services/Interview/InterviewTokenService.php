<?php

declare(strict_types=1);

namespace App\Services\Interview;

use App\DTOs\Interview\IssueInterviewTokenData;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final readonly class InterviewTokenService
{
    public function __construct(
        private InterviewPromptContextService $promptContextService,
    ) {}

    /**
     * @return array{token: string, room: string, prompt_context: array<string, mixed>}
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

        $promptContext = $this->promptContextService->build($user, $data);
        /** @var array{user_id:int,name:string,job_role:string} $candidate */
        $candidate = $promptContext['candidate'];
        /** @var array{mode:string,type:string} $interview */
        $interview = $promptContext['interview'];
        /** @var array{question_count:int,planned_duration_seconds:int,concise_feedback:bool} $session */
        $session = $promptContext['session'];
        /** @var array{context_notes:string} $instructions */
        $instructions = $promptContext['instructions'];

        $response = Http::acceptJson()
            ->asJson()
            ->withHeaders([
                'X-Internal-Token' => $sharedSecret,
            ])
            ->connectTimeout(5)
            ->timeout(10)
            ->retry(2, 200)
            ->post(rtrim($tokenServerUrl, '/').'/internal/issue-token', [
                'user_id' => $candidate['user_id'],
                'email' => (string) $user->email,
                'name' => $candidate['name'],
                'job_role' => $candidate['job_role'],
                'interview_mode' => $interview['mode'],
                'interview_type' => $interview['type'],
                'question_count' => $session['question_count'],
                'planned_duration_seconds' => $session['planned_duration_seconds'],
                'concise_feedback' => $session['concise_feedback'],
                'context_notes' => $instructions['context_notes'],
                'prompt_context' => $promptContext,
            ])
            ->throw();

        /** @var mixed $body */
        $body = $response->json();
        if (! is_array($body) || ! isset($body['token'], $body['room'])) {
            throw new RuntimeException('Token server returned an invalid response.');
        }

        return [
            'token' => (string) $body['token'],
            'room' => (string) $body['room'],
            'prompt_context' => $promptContext,
        ];
    }
}
