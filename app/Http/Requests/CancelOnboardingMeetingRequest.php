<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Central\OnboardingAppointment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelOnboardingMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $appointment = $this->route('onboardingAppointment');

        if (! $appointment instanceof OnboardingAppointment) {
            return false;
        }

        if ($this->user() !== null) {
            return true;
        }

        return $appointment->matchesAccessToken($this->accessToken());
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'token' => [
                Rule::requiredIf($this->user() === null),
                'string',
                'min:32',
            ],
        ];
    }

    private function accessToken(): string
    {
        return (string) $this->input('token', $this->query('token', ''));
    }
}
