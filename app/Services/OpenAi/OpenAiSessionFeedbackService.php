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
     * Turn a live interview transcript into structured coaching feedback.
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

        if (! $this->transcriptContainsSubstantiveCandidateAnswers($messages)) {
            return $this->fallbackPayload(
                $jobRole,
                $interviewType,
                $durationSeconds,
                'We did not capture enough spoken answers from you to score — the transcript looks like interviewer prompts '
               .'only (or replies were extremely short). Unmute your microphone next time so your replies are transcribed '
               .'and analyzed.',
                [
                    'insufficient_candidate_input' => true,
                    'session_summary_line' => $this->formatSessionSummaryUnavailableLine(
                        $durationSeconds,
                        'no answers to score',
                    ),
                ],
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
            You are a senior hiring manager writing a private debrief — strict, specific, and blunt where the transcript warrants it. The transcript is from a finished live conversation that must be graded like a real screening or onsite loop, not a friendly chatbot drill.

            Assume the candidate showed up as they would with a serious employer. Score and coach at that bar. Never frame the session as pretend, low-stakes practice, or a game in any field.

            VOICE (mandatory — reads like a coach, not marketing copy):
            - No generic praise ("Great job", "Love that you", "It's clear you are passionate"). No filler openers or sign-offs.
            - Avoid AI-tell phrases: do not use "leverage", "delve", "robust", "showcase", "in today's", "it's important to note", "journey", "space", "landscape", "synergy", "circle back".
            - strengths and growth_areas: each bullet must tie to something concrete said or not said in the transcript; if you cannot cite behavior, omit the bullet.
            - top_insight: one dense paragraph, past tense, what would you tell the panel about signal vs noise — no enthusiasm, no bullet list inside the string. If you use the candidate's given name (only when it appears naturally in the transcript), use it **at most once** in this paragraph; after that refer with **they/them/the candidate**. Do not repeat their name across clauses — it reads like a mail-merge, not a hiring debrief.
            - suggested_rewrite: first person as the candidate, plain spoken English, 3-10 sentences as needed; keep their facts and role; add missing structure (situation, ownership, trade-offs, metrics, outcome). No markdown, no numbered lists inside strings.

            Return ONLY valid JSON matching this shape (no markdown fences, no extra keys, no extra text):
            {
              "overall_score": <number 0-10 with one decimal>,
              "breakdown": [
                {"label": "Clarity", "value": <0-10 one decimal>},
                {"label": "Structure", "value": <0-10 one decimal>},
                {"label": "Depth", "value": <0-10 one decimal>},
                {"label": "Impact", "value": <0-10 one decimal>},
                {"label": "Pacing", "value": <0-10 one decimal>}
              ],
              "strengths": [<3-6 short bullet strings, transcript-specific>],
              "growth_areas": [<3-6 actionable bullet strings, transcript-specific>],
              "top_insight": "<one paragraph, hiring-committee tone>",
              "improved_answers": [
                {
                  "question": "<interviewer question or tight paraphrase>",
                  "your_answer_snippet": "<short quote or neutral paraphrase of what they actually said>",
                  "suggested_rewrite": "<stronger answer in their voice at hiring bar>"
                }
              ],
              "headline_title": "<role + interview type at real hiring bar, e.g. Senior PM / Behavioral>",
              "session_summary_line": "<one line e.g. 12 min · 6 exchanges · debrief>"
            }

            Scores: rigorous versus real hiring expectations — reward outcomes, clarity, metrics, ownership, trade-offs; penalize fluff, hand-waving, and unanswered depth.

            improved_answers — COMPREHENSIVE (this is where most coaching value lives):
            - Walk the transcript in chronological order. For each distinct interviewer question (including material follow-ups) where the candidate's reply was below strong-loop quality — vague, unstructured, missing stakes or result, dodged the ask, too thin to defend in a debrief, or rambling without a point — emit exactly one object. Do NOT collapse multiple weak answers into one row. Do NOT stop at two or three rows out of habit.
            - If only one or two answers were weak, return only those. If six were weak, return six. If every substantive answer cleared the bar, return an empty array.
            - Hard cap: at most 18 objects total (if more than 18 misses exist, prioritize the worst signal in order through the cap).
            - your_answer_snippet must be faithful to the transcript (quote or tight paraphrase); do not invent dialogue they did not say.
        PROMPT;

        $user = implode("\n", [
            'Job role: '.$jobRole,
            'Interview type: '.$interviewType,
            'Approx. duration (seconds): '.$durationSeconds,
            'Expected question target (session plan): '.$expectedQuestions.' — judge the actual transcript; improved_answers must list every below-bar reply in order (see system rules), not a small sample.',
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
                    'temperature' => 0.35,
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
     * @param  array<int, array{speaker?: string, text?: string|null}>  $messages
     */
    private function transcriptContainsSubstantiveCandidateAnswers(array $messages): bool
    {
        $minChars = 15;

        foreach ($messages as $m) {
            if (($m['speaker'] ?? '') !== 'user') {
                continue;
            }

            $normalized = preg_replace('/\s+/u', ' ', trim((string) ($m['text'] ?? '')));

            if (! is_string($normalized)) {
                continue;
            }

            if (mb_strlen($normalized) >= $minChars) {
                return true;
            }
        }

        return false;
    }

    private function formatSessionSummaryUnavailableLine(int $durationSeconds, string $suffix): string
    {
        $minutes = max(1, (int) round($durationSeconds / 60));

        return $minutes.' min · '.$suffix;
    }

    private const MAX_IMPROVED_ANSWERS = 18;

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
            'improved_answers' => $this->normalizeImprovedAnswers($raw['improved_answers'] ?? []),
            'headline_title' => (string) ($raw['headline_title'] ?? ($jobRole.' / '.$interviewType)),
            'session_summary_line' => (string) ($raw['session_summary_line'] ?? ''),
        ];
    }

    /**
     * @return array<int, array{question: string, your_answer_snippet: string, suggested_rewrite: string}>
     */
    private function normalizeImprovedAnswers(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $out = [];

        foreach ($items as $row) {
            if (! is_array($row)) {
                continue;
            }

            $question = trim((string) ($row['question'] ?? ''));
            $snippet = trim((string) ($row['your_answer_snippet'] ?? ''));
            $rewrite = trim((string) ($row['suggested_rewrite'] ?? ''));

            if ($question === '' || $rewrite === '') {
                continue;
            }

            $out[] = [
                'question' => $question,
                'your_answer_snippet' => $snippet !== '' ? $snippet : '—',
                'suggested_rewrite' => $rewrite,
            ];

            if (count($out) >= self::MAX_IMPROVED_ANSWERS) {
                break;
            }
        }

        return array_values($out);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function fallbackPayload(
        string $jobRole,
        string $interviewType,
        int $durationSeconds,
        string $topInsight,
        array $overrides = [],
    ): array {
        return array_merge([
            'overall_score' => null,
            'breakdown' => [],
            'strengths' => [],
            'growth_areas' => [],
            'top_insight' => $topInsight,
            'improved_answers' => [],
            'headline_title' => $jobRole.' / '.$interviewType,
            'session_summary_line' => $this->formatSessionSummaryUnavailableLine(
                $durationSeconds,
                'feedback unavailable',
            ),
            'partial' => true,
        ], $overrides);
    }
}
