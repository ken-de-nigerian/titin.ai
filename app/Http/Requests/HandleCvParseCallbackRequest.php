<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class HandleCvParseCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'cv_id' => ['required', 'integer'],
            'user_id' => ['required', 'integer'],
            'status' => ['required', 'string', 'in:parsed,failed'],
            'schema_version' => ['nullable', 'string', 'max:20'],
            'profile' => ['nullable', 'array'],
        ];
    }
}
