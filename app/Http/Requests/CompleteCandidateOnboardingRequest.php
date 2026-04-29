<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class CompleteCandidateOnboardingRequest extends FormRequest
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
        $types = array_keys((array) config('settings.interview.types', []));
        $allowedTypes = implode(',', $types !== [] ? $types : [(string) config('settings.interview.default_type', 'mixed')]);
        $levels = array_keys((array) config('settings.seniority.levels', []));
        $allowedLevels = implode(',', $levels !== [] ? $levels : [(string) config('settings.seniority.default_level', 'mid_level')]);

        return [
            'job_role' => ['required', 'string', 'max:200'],
            'interview_type' => ['required', 'string', "in:{$allowedTypes}", 'max:64'],
            'seniority_level' => ['required', 'string', "in:{$allowedLevels}", 'max:64'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }
}
