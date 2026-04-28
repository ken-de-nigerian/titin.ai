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
        return [
            'job_role' => ['required', 'string', 'max:200'],
            'interview_type' => ['required', 'string', 'in:behavioral,technical,role_specific', 'max:64'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }
}

