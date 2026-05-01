<?php

declare(strict_types=1);

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\ParsedCvProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class InterviewContextController extends Controller
{
    public function __invoke(Request $request, User $user): JsonResponse
    {
        $expected = (string) config('services.livekit.internal_secret');
        $provided = (string) $request->header('X-Internal-Token', '');

        if ($expected === '' || $provided !== $expected) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        $contextLines = [
            sprintf('Candidate: %s', $user->name),
            sprintf('Target role: %s', $user->job_role ?? 'Software Engineer'),
            sprintf('Interview type preference: %s', $user->interview_type ?? 'mixed'),
        ];

        if (is_string($user->resume_path) && $user->resume_path !== '') {
            $contextLines[] = 'Candidate has uploaded a resume. Prioritize role-relevant, experience-based questions.';
        } else {
            $contextLines[] = 'No parsed resume context available yet. Start broad and adapt from answers.';
        }

        $latestParsedProfile = ParsedCvProfile::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();
        if ($latestParsedProfile !== null && is_array($latestParsedProfile->profile_json)) {
            $profile = $latestParsedProfile->profile_json;
            $skills = $profile['skills'] ?? null;
            if (is_array($skills) && $skills !== []) {
                $topSkills = array_slice(array_map(static fn ($value): string => (string) $value, $skills), 0, 8);
                $contextLines[] = sprintf('Top skills: %s', implode(', ', $topSkills));
            }
            $summary = $profile['summary'] ?? null;
            if (is_string($summary) && trim($summary) !== '') {
                $contextLines[] = 'CV summary: '.Str::limit(trim($summary), 700);
            }
        }

        return response()->json([
            'user_id' => $user->id,
            'context_notes' => implode("\n", $contextLines),
        ]);
    }
}
