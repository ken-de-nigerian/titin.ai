<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeSessionFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'messages' => ['array', 'max:500'],
            'messages.*.speaker' => ['required', 'string', 'in:user,agent'],
            'messages.*.text' => ['required', 'string', 'max:20000'],
            'messages.*.at' => ['nullable', 'string', 'max:40'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'job_role' => ['nullable', 'string', 'max:200'],
            'interview_type' => ['nullable', 'string', 'max:64'],
            'question_count' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
