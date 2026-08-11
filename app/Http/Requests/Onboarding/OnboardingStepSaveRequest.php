<?php

declare(strict_types=1);

namespace App\Http\Requests\Onboarding;

use App\Onboarding\Services\ProspectOnboardingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class OnboardingStepSaveRequest extends FormRequest
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
            'stepKey' => ['required', 'string', Rule::in(ProspectOnboardingService::STEP_KEYS)],
            'payload' => ['required', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->fails()) {
                return;
            }

            $stepKey = (string) $this->input('stepKey');
            /** @var array<string, mixed> $payload */
            $payload = Arr::wrap($this->input('payload', []));

            $nestedValidator = validator($payload, $this->stepPayloadRules($stepKey));

            if (! $nestedValidator->fails()) {
                return;
            }

            foreach ($nestedValidator->errors()->toArray() as $field => $messages) {
                foreach ($messages as $message) {
                    $validator->errors()->add(sprintf('payload.%s', $field), $message);
                }
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function stepPayloadRules(string $stepKey): array
    {
        return match ($stepKey) {
            'business' => [
                'contactName' => ['required', 'string', 'max:120'],
                'businessName' => ['required', 'string', 'max:160'],
                'businessEmail' => ['required', 'email:rfc', 'max:160'],
                'phone' => ['required', 'string', 'max:40'],
                'website' => ['nullable', 'url:http,https', 'max:255'],
                'industry' => ['required', 'string', 'max:80'],
                'businessType' => ['nullable', 'string', 'max:120'],
                'location' => ['nullable', 'string', 'max:160'],
                'contactRole' => ['nullable', 'string', 'max:80'],
            ],
            'project' => [
                'projectTypes' => ['required', 'array', 'min:1', 'max:8'],
                'projectTypes.*' => ['required', 'string', 'max:80'],
                'projectTypeOther' => ['nullable', 'string', 'max:255'],
                'projectSummary' => ['nullable', 'string', 'max:500'],
            ],
            'overview' => [
                'whatToBuild' => ['required', 'string', 'max:4000'],
                'problemToSolve' => ['required', 'string', 'max:4000'],
                'businessGoals' => ['required', 'string', 'max:4000'],
                'primaryUsers' => ['required', 'string', 'max:1200'],
            ],
            'features' => [
                'requestedFeatures' => ['required', 'array', 'min:1', 'max:20'],
                'requestedFeatures.*' => ['required', 'string', 'max:160'],
                'otherFeatures' => ['nullable', 'string', 'max:3000'],
            ],
            'assets' => [
                'existingAssets' => ['nullable', 'array', 'max:20'],
                'existingAssets.*' => ['required', 'string', 'max:160'],
                'currentWebsiteUrl' => ['nullable', 'url:http,https', 'max:255'],
                'domainName' => ['nullable', 'string', 'max:255'],
                'hostingProvider' => ['nullable', 'string', 'max:255'],
                'existingPlatform' => ['nullable', 'string', 'max:255'],
                'existingSoftware' => ['nullable', 'string', 'max:255'],
                'knownIntegrations' => ['nullable', 'string', 'max:3000'],
                'dataNotes' => ['nullable', 'string', 'max:3000'],
            ],
            'branding' => [
                'hasLogo' => ['nullable', 'boolean'],
                'hasBrandColors' => ['nullable', 'boolean'],
                'hasBrandGuidelines' => ['nullable', 'boolean'],
                'inspirationLinks' => ['nullable', 'array', 'max:8'],
                'inspirationLinks.*' => ['nullable', 'url:http,https', 'max:500'],
                'inspirationNotes' => ['nullable', 'string', 'max:3000'],
                'designDirection' => ['nullable', 'string', 'max:3000'],
            ],
            'timeline' => [
                'targetLaunchDate' => ['nullable', 'date'],
                'timelineExpectation' => ['required', 'string', 'max:80'],
                'urgency' => ['required', 'string', 'max:40'],
                'deadlineContext' => ['nullable', 'string', 'max:3000'],
                'budgetExpectation' => ['nullable', 'string', 'max:120'],
            ],
            'additional' => [
                'additionalNotes' => ['nullable', 'string', 'max:5000'],
            ],
            default => [],
        };
    }
}
