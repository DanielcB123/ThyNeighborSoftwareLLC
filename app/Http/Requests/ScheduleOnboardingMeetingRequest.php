<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Central\OnboardingAppointment;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ScheduleOnboardingMeetingRequest extends FormRequest
{
    /**
     * @var list<string>
     */
    private const USA_TIMEZONES = [
        'America/New_York',
        'America/Chicago',
        'America/Denver',
        'America/Phoenix',
        'America/Los_Angeles',
        'America/Anchorage',
        'Pacific/Honolulu',
    ];

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
            'business_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email:rfc', 'max:255'],
            'meeting_date' => ['required', 'date_format:Y-m-d'],
            'meeting_time' => ['required', 'date_format:H:i'],
            'timezone' => ['required', 'timezone:all', Rule::in(self::USA_TIMEZONES)],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:180'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $meetingDate = (string) $this->input('meeting_date', '');
            $meetingTime = (string) $this->input('meeting_time', '');
            $timezone = (string) $this->input('timezone', '');

            $scheduledAt = CarbonImmutable::createFromFormat(
                'Y-m-d H:i',
                sprintf('%s %s', $meetingDate, $meetingTime),
                $timezone,
            );

            if (! $scheduledAt instanceof CarbonImmutable) {
                $validator->errors()->add('meeting_time', 'Please provide a valid meeting date/time.');
                return;
            }

            if ($scheduledAt->lessThanOrEqualTo(CarbonImmutable::now($timezone))) {
                $validator->errors()->add('meeting_time', 'Please choose a future meeting time.');
            }
        });
    }

    private function accessToken(): string
    {
        return (string) $this->input('token', $this->query('token', ''));
    }
}
