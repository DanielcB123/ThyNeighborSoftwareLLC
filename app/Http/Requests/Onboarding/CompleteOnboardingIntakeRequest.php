<?php

declare(strict_types=1);

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class CompleteOnboardingIntakeRequest extends FormRequest
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
            'sessionToken' => ['required', 'string', 'regex:/^[A-Za-z0-9]{64}$/', 'exists:onboarding_sessions,access_token'],
        ];
    }
}
