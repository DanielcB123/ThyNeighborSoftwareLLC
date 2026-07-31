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
            'sessionToken' => ['required', 'string', 'max:80', 'exists:onboarding_sessions,access_token'],
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
            'project' => [
                'projectDirection' => ['required', 'string', 'max:80'],
                'projectDirectionNotes' => ['nullable', 'string', 'max:800'],
            ],
            'business' => [
                'contactName' => ['required', 'string', 'max:120'],
                'businessName' => ['required', 'string', 'max:160'],
                'businessEmail' => ['required', 'email:rfc,dns', 'max:160'],
                'phone' => ['required', 'string', 'max:40'],
                'industry' => ['required', 'string', 'max:80'],
                'industryOther' => ['nullable', 'string', 'max:120'],
                'businessLocation' => ['required', 'string', 'max:160'],
                'locationCount' => ['required', 'integer', 'min:1', 'max:500'],
                'teamSize' => ['required', 'string', 'max:40'],
                'roleInBusiness' => ['required', 'string', 'max:80'],
                'additionalStakeholders' => ['nullable', 'array', 'max:10'],
                'additionalStakeholders.*.name' => ['nullable', 'string', 'max:120'],
                'additionalStakeholders.*.email' => ['nullable', 'email:rfc,dns', 'max:160'],
                'additionalStakeholders.*.role' => ['nullable', 'string', 'max:80'],
                'additionalStakeholders.*.inviteLater' => ['nullable', 'boolean'],
            ],
            'goals' => [
                'goals' => ['required', 'array', 'min:1', 'max:10'],
                'goals.*' => ['required', 'string', 'max:160'],
                'goalsOther' => ['nullable', 'string', 'max:1000'],
                'currentProblems' => ['required', 'string', 'max:4000'],
                'successLooksLike' => ['required', 'string', 'max:4000'],
                'knownConstraints' => ['nullable', 'string', 'max:4000'],
                'timelineComfort' => ['required', 'string', 'max:80'],
                'budgetComfort' => ['required', 'string', 'max:120'],
            ],
            'preparation' => [
                'existingAssets' => ['nullable', 'array', 'max:20'],
                'existingAssets.*' => ['required', 'string', 'max:160'],
                'importantLinks' => ['nullable', 'array', 'max:10'],
                'importantLinks.*' => ['nullable', 'url:http,https', 'max:500'],
                'notesForTeam' => ['nullable', 'string', 'max:4000'],
                'uploadedMaterials' => ['nullable', 'array', 'max:20'],
                'uploadedMaterials.*.originalName' => ['required', 'string', 'max:255'],
                'uploadedMaterials.*.storagePath' => ['required', 'string', 'max:255'],
                'uploadedMaterials.*.mimeType' => ['required', 'string', 'max:120'],
                'uploadedMaterials.*.size' => ['required', 'integer', 'min:1'],
                'uploadedMaterials.*.uploadedAt' => ['required', 'date'],
            ],
            'meeting' => [
                'meetingFormat' => ['required', Rule::in(['video', 'phone', 'in-person'])],
                'timezone' => ['required', 'timezone'],
                'preferredStartDate' => ['required', 'date', 'after_or_equal:today'],
                'preferredEndDate' => ['required', 'date', 'after_or_equal:preferredStartDate'],
                'availabilityNotes' => ['required', 'string', 'max:4000'],
                'attendees' => ['required', 'array', 'min:1', 'max:8'],
                'attendees.*.name' => ['required', 'string', 'max:120'],
                'attendees.*.email' => ['nullable', 'email:rfc,dns', 'max:160'],
                'attendees.*.role' => ['nullable', 'string', 'max:80'],
            ],
            default => [],
        };
    }
}
