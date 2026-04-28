<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Http\RedirectValidationFlash;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

final class StartRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return config('settings.register.enabled');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->input('email'))),
            ]);
        }

        if ($this->has('name')) {
            $this->merge([
                'name' => trim((string) $this->input('name')),
            ]);
        }
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            RedirectValidationFlash::back(
                __('Registration disabled. Please contact the administrator.'),
                [],
                __('Error'),
                5000,
                $this->only(['name', 'email']),
            ),
        );
    }
}
