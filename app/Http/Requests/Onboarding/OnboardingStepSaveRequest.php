<?php

declare(strict_types=1);

namespace App\Http\Requests\Onboarding;

use App\Onboarding\Services\ProspectOnboardingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OnboardingStepSaveRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

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
            'stepKey' => ['required', 'string', Rule::in(ProspectOnboardingService::STEP_KEYS)],
            'payload' => ['required', 'array'],
        ];
    }
}
