<?php

declare(strict_types=1);

namespace App\Services\Onboarding;

use App\Models\Central\OnboardingAppointment;
use App\Notifications\OnboardingMeetingScheduledNotification;
use App\Services\Onboarding\Data\OnboardingMeetingScheduleData;
use App\Services\Zoom\Data\ZoomMeetingInput;
use App\Services\Zoom\ZoomMeetingService;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

final class OnboardingMeetingScheduler
{
    public function __construct(
        private readonly ZoomMeetingService $zoomMeetingService,
    ) {
    }

    public function schedule(
        OnboardingAppointment $appointment,
        OnboardingMeetingScheduleData $scheduleData,
    ): OnboardingAppointment {
        $scheduledAtInTimezone = CarbonImmutable::createFromFormat(
            'Y-m-d H:i',
            sprintf('%s %s', $scheduleData->meetingDate, $scheduleData->meetingTime),
            $scheduleData->timezone,
        );

        if (! $scheduledAtInTimezone instanceof CarbonImmutable) {
            throw new InvalidArgumentException('Invalid meeting date/time payload.');
        }

        $scheduledAtUtc = $scheduledAtInTimezone->setTimezone('UTC');
        $notificationPayload = null;

        /** @var OnboardingAppointment $updated */
        $updated = DB::connection($appointment->getConnectionName())->transaction(
            function () use ($appointment, $scheduleData, $scheduledAtUtc, &$notificationPayload): OnboardingAppointment {
                /** @var OnboardingAppointment $locked */
                $locked = OnboardingAppointment::query()
                    ->whereKey($appointment->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($this->isIdempotentRequest($locked, $scheduleData, $scheduledAtUtc)) {
                    return $locked;
                }

                $zoomMeetingInput = new ZoomMeetingInput(
                    topic: sprintf('Client Onboarding - %s', $scheduleData->businessName),
                    scheduledAt: $scheduledAtUtc,
                    timezone: $scheduleData->timezone,
                    durationMinutes: $scheduleData->durationMinutes,
                );

                $zoomMeeting = $this->shouldUpdateExistingZoomMeeting($locked)
                    ? $this->zoomMeetingService->updateMeeting((string) $locked->zoom_meeting_id, $zoomMeetingInput)
                    : $this->zoomMeetingService->createMeeting($zoomMeetingInput);

                $locked->fill([
                    'business_name' => $scheduleData->businessName,
                    'contact_name' => $scheduleData->contactName,
                    'contact_email' => $scheduleData->contactEmail,
                    'status' => 'scheduled',
                    'scheduled_at' => $scheduledAtUtc,
                    'timezone' => $scheduleData->timezone,
                    'duration_minutes' => $scheduleData->durationMinutes,
                    'zoom_meeting_id' => $zoomMeeting->id,
                    'zoom_join_url' => $zoomMeeting->joinUrl,
                    'zoom_start_url' => $zoomMeeting->startUrl,
                    'zoom_passcode' => $zoomMeeting->passcode,
                    'zoom_status' => $zoomMeeting->status ?? 'scheduled',
                    'cancelled_at' => null,
                    'last_zoom_synced_at' => now()->utc(),
                ]);
                $locked->save();

                if ($locked->contact_email !== null && $locked->zoom_join_url !== null) {
                    $notificationPayload = [
                        'contact_email' => $locked->contact_email,
                        'business_name' => $locked->business_name,
                        'scheduled_at' => $scheduledAtUtc,
                        'timezone' => $locked->timezone,
                        'zoom_join_url' => $locked->zoom_join_url,
                    ];
                }

                return $locked;
            }
        );

        if (is_array($notificationPayload)) {
            Notification::route('mail', $notificationPayload['contact_email'])
                ->notify(new OnboardingMeetingScheduledNotification(
                    businessName: (string) $notificationPayload['business_name'],
                    scheduledAtUtc: $notificationPayload['scheduled_at'],
                    timezone: (string) $notificationPayload['timezone'],
                    joinUrl: (string) $notificationPayload['zoom_join_url'],
                ));
        }

        return $updated->fresh() ?? $updated;
    }

    public function cancel(OnboardingAppointment $appointment): OnboardingAppointment
    {
        /** @var OnboardingAppointment $updated */
        $updated = DB::connection($appointment->getConnectionName())->transaction(function () use ($appointment): OnboardingAppointment {
            /** @var OnboardingAppointment $locked */
            $locked = OnboardingAppointment::query()
                ->whereKey($appointment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === 'cancelled') {
                return $locked;
            }

            if ($this->shouldUpdateExistingZoomMeeting($locked)) {
                $this->zoomMeetingService->deleteMeeting((string) $locked->zoom_meeting_id);
            }

            $locked->fill([
                'status' => 'cancelled',
                'zoom_status' => 'cancelled',
                'cancelled_at' => now()->utc(),
                'last_zoom_synced_at' => now()->utc(),
            ]);
            $locked->save();

            return $locked;
        });

        return $updated->fresh() ?? $updated;
    }

    private function shouldUpdateExistingZoomMeeting(OnboardingAppointment $appointment): bool
    {
        return $appointment->zoom_meeting_id !== null && $appointment->zoom_status !== 'cancelled';
    }

    private function isIdempotentRequest(
        OnboardingAppointment $appointment,
        OnboardingMeetingScheduleData $scheduleData,
        CarbonImmutable $scheduledAtUtc,
    ): bool {
        if ($appointment->status !== 'scheduled' || ! $this->shouldUpdateExistingZoomMeeting($appointment)) {
            return false;
        }

        if ($appointment->scheduled_at === null) {
            return false;
        }

        return $appointment->business_name === $scheduleData->businessName
            && $appointment->contact_name === $scheduleData->contactName
            && $appointment->contact_email === $scheduleData->contactEmail
            && $appointment->timezone === $scheduleData->timezone
            && (int) $appointment->duration_minutes === $scheduleData->durationMinutes
            && $appointment->scheduled_at->equalTo($scheduledAtUtc);
    }
}
