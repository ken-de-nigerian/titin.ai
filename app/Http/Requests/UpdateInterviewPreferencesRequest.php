<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateInterviewPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $interviewTypes = array_keys((array) config('settings.interview.types', []));
        $allowedInterviewTypes = implode(',', $interviewTypes !== [] ? $interviewTypes : [(string) config('settings.interview.default_type', 'mixed')]);
        $seniorityLevels = array_keys((array) config('settings.seniority.levels', []));
        $allowedSeniorityLevels = implode(',', $seniorityLevels !== [] ? $seniorityLevels : [(string) config('settings.seniority.default_level', 'mid_level')]);
        $minDuration = (int) config('settings.interview.min_duration_minutes', 5);
        $maxDuration = (int) config('settings.interview.max_duration_minutes', 120);

        return [
            'job_role' => ['nullable', 'string', 'max:200'],
            'interview_type' => ['required', 'string', "in:$allowedInterviewTypes", 'max:64'],
            'seniority_level' => ['required', 'string', "in:$allowedSeniorityLevels", 'max:64'],
            'prefers_concise_feedback' => ['required', 'boolean'],
            'interview_duration_minutes' => ['required', 'integer', 'min:'.$minDuration, 'max:'.$maxDuration],
        ];
    }
}
