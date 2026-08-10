<?php

declare(strict_types=1);

namespace App\Services\Zoom;

use App\Services\Zoom\Data\ZoomMeetingData;
use App\Services\Zoom\Data\ZoomMeetingInput;
use App\Services\Zoom\Exceptions\ZoomIntegrationException;
use Illuminate\Support\Arr;

final class ZoomMeetingService
{
    public function __construct(
        private readonly ZoomClient $zoomClient,
    ) {
    }

    public function createMeeting(ZoomMeetingInput $input): ZoomMeetingData
    {
        $hostUser = $this->hostUser();
        $payload = $this->meetingPayload($input);
        $response = $this->zoomClient->createMeetingForHost($hostUser, $payload);

        return $this->toMeetingData($response);
    }

    public function updateMeeting(string $meetingId, ZoomMeetingInput $input): ZoomMeetingData
    {
        $this->zoomClient->updateMeeting($meetingId, $this->meetingPayload($input));
        $response = $this->zoomClient->getMeeting($meetingId);

        return $this->toMeetingData($response);
    }

    public function deleteMeeting(string $meetingId): void
    {
        $this->zoomClient->deleteMeeting($meetingId);
    }

    private function hostUser(): string
    {
        $hostUser = (string) config('services.zoom.host_user', '');

        if ($hostUser === '') {
            throw new ZoomIntegrationException(
                'Zoom configuration missing host_user.',
                'Zoom integration is not configured yet. Please contact support.',
            );
        }

        return $hostUser;
    }

    /**
     * @return array<string, mixed>
     */
    private function meetingPayload(ZoomMeetingInput $input): array
    {
        return [
            'topic' => $input->topic,
            'type' => 2,
            'start_time' => $input->scheduledAt->setTimezone($input->timezone)->format('Y-m-d\TH:i:s'),
            'duration' => $input->durationMinutes,
            'timezone' => $input->timezone,
            'settings' => [
                'join_before_host' => false,
                'waiting_room' => true,
                'participant_video' => false,
                'host_video' => true,
                'mute_upon_entry' => true,
                'approval_type' => 2,
                'audio' => 'both',
                'auto_recording' => 'none',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function toMeetingData(array $response): ZoomMeetingData
    {
        return new ZoomMeetingData(
            id: (string) Arr::get($response, 'id'),
            joinUrl: Arr::get($response, 'join_url'),
            startUrl: Arr::get($response, 'start_url'),
            passcode: Arr::get($response, 'password'),
            status: Arr::get($response, 'status'),
        );
    }
}
