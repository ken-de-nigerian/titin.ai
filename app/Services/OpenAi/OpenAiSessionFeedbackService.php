<?php

declare(strict_types=1);

namespace App\Services\OpenAi;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final class OpenAiSessionFeedbackService
{
    /**
     * Turn a mock-interview transcript into structured coaching feedback.
     * Uses OPENAI_FEEDBACK_MODEL (default GPT-4o): one call per paid session end.
     *
     * @param  array<int, array{speaker: string, text: string, at?: string|null}>  $messages
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     */
    public function analyze(
        array $messages,
        string $jobRole,
        string $interviewType,
        int $durationSeconds,
        int $expectedQuestions,
    ): array {
        $apiKey = config('services.openai.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('OpenAI is not configured. Set OPENAI_API_KEY in your .env file.');
        }

        if ($messages === []) {
            return $this->fallbackPayload(
                $jobRole,
                $interviewType,
                $durationSeconds,
                'No transcript was captured for this session. Complete a voice session and try again.',
            );
        }

        $model = (string) config('services.openai.feedback_model', 'gpt-4o');

        $lines = [];
        foreach ($messages as $m) {
            $who = ($m['speaker'] ?? '') === 'user' ? 'Candidate' : 'Interviewer';
            $lines[] = $who.': '.trim((string) ($m['text'] ?? ''));
        }

        $transcript = implode("\n", $lines);

        $system = <<<'PROMPT'
            You are an expert interview coach. The user paid for a mock interview session.
            Return ONLY valid JSON matching this shape (no markdown, no extra text):
            {
              "overall_score": <number 0-10 with one decimal>,
              "breakdown": [
                {"label": "Clarity", "value": <0-10 one decimal>},
                {"label": "Structure", "value": <0-10 one decimal>},
                {"label": "Depth", "value": <0-10 one decimal>},
                {"label": "Impact", "value": <0-10 one decimal>},
                {"label": "Pacing", "value": <0-10 one decimal>}
              ],
              "strengths": [<3-5 short bullet strings, specific to this transcript>],
              "growth_areas": [<3-5 actionable bullet strings>],
              "top_insight": "<one paragraph, specific insight referencing their answers>",
              "improved_answers": [
                {
                  "question": "<interviewer question text or paraphrase>",
                  "your_answer_snippet": "<short quote or paraphrase of candidate>",
                  "suggested_rewrite": "<stronger concise answer in their voice>"
                }
              ],
              "headline_title": "<role + interview type, e.g. Senior PM / Behavioral>",
              "session_summary_line": "<one line e.g. 12 minutes · 4 exchanges · tailored feedback>"
            }
            Scores must be fair: reward structure and metrics; note vague stories. improved_answers: 1-3 items max, only when there is enough transcript.
        PROMPT;

        $user = implode("\n", [
            'Job role: '.$jobRole,
            'Interview type: '.$interviewType,
            'Approx. duration (seconds): '.$durationSeconds,
            'Expected question target: '.$expectedQuestions,
            'Transcript:',
            $transcript,
        ]);

        try {
            $response = Http::withToken($apiKey)
                ->timeout(120)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                    'temperature' => 0.45,
                ]);

            $response->throw();

            $content = $response->json('choices.0.message.content');

            if (! is_string($content) || $content === '') {
                throw new RuntimeException('Empty response from OpenAI.');
            }

            /** @var array<string, mixed>|null $decoded */
            $decoded = json_decode($content, true);

            if (! is_array($decoded)) {
                throw new RuntimeException('Invalid JSON from OpenAI.');
            }

            return $this->normalizePayload($decoded, $jobRole, $interviewType);
        } catch (RequestException $e) {
            Log::warning('OpenAI session feedback failed', ['message' => $e->getMessage()]);

            return $this->fallbackPayload(
                $jobRole,
                $interviewType,
                $durationSeconds,
                'We could not generate AI feedback right now. Your session still counts — try again in a moment.',
            );
        }
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function normalizePayload(array $raw, string $jobRole, string $interviewType): array
    {
        return [
            'overall_score' => round((float) ($raw['overall_score'] ?? 7), 1),
            'breakdown' => $raw['breakdown'] ?? [],
            'strengths' => array_values((array) ($raw['strengths'] ?? [])),
            'growth_areas' => array_values((array) ($raw['growth_areas'] ?? [])),
            'top_insight' => (string) ($raw['top_insight'] ?? ''),
            'improved_answers' => array_values((array) ($raw['improved_answers'] ?? [])),
            'headline_title' => (string) ($raw['headline_title'] ?? ($jobRole.' / '.$interviewType)),
            'session_summary_line' => (string) ($raw['session_summary_line'] ?? ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fallbackPayload(
        string $jobRole,
        string $interviewType,
        int $durationSeconds,
        string $topInsight,
    ): array {
        $minutes = max(1, (int) round($durationSeconds / 60));

        return [
            'overall_score' => null,
            'breakdown' => [],
            'strengths' => [],
            'growth_areas' => [],
            'top_insight' => $topInsight,
            'improved_answers' => [],
            'headline_title' => $jobRole.' / '.$interviewType,
            'session_summary_line' => $minutes.' min · feedback unavailable',
            'partial' => true,
        ];
    }
}
