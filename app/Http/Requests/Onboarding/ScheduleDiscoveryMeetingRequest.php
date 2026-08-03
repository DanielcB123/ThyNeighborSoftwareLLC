<?php

declare(strict_types=1);

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScheduleDiscoveryMeetingRequest extends FormRequest
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
            'sessionToken' => ['required', 'string', 'regex:/^[A-Za-z0-9]{64}$/'],
            'payload' => ['required', 'array'],
            'payload.meetingFormat' => ['required', Rule::in(['video', 'phone', 'in-person'])],
            'payload.timezone' => ['required', 'timezone'],
            'payload.preferredStartDate' => ['required', 'date', 'after_or_equal:today'],
            'payload.preferredEndDate' => ['required', 'date', 'after_or_equal:payload.preferredStartDate'],
            'payload.availabilityNotes' => ['required', 'string', 'max:4000'],
            'payload.attendees' => ['required', 'array', 'min:1', 'max:8'],
            'payload.attendees.*.name' => ['required', 'string', 'max:120'],
            'payload.attendees.*.email' => ['nullable', 'email:rfc,dns', 'max:160'],
            'payload.attendees.*.role' => ['nullable', 'string', 'max:80'],
        ];
    }
}
