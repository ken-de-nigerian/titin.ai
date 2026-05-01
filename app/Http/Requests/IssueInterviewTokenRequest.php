<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class IssueInterviewTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        $types = array_keys((array) config('settings.interview.types', []));
        $allowedTypes = implode(',', $types !== [] ? $types : [(string) config('settings.interview.default_type', 'mixed')]);

        return [
            'job_role' => ['nullable', 'string', 'max:200'],
            'interview_type' => ['nullable', 'string', "in:$allowedTypes", 'max:64'],
        ];
    }
}
