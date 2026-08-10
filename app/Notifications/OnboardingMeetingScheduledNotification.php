<?php

declare(strict_types=1);

namespace App\Notifications;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OnboardingMeetingScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $businessName,
        private readonly CarbonInterface $scheduledAtUtc,
        private readonly string $timezone,
        private readonly string $joinUrl,
    ) {
    }

    /**
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * @param  mixed  $notifiable
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $localized = $this->scheduledAtUtc->copy()->setTimezone($this->timezone);
        $dateLabel = $localized->format('l, F j, Y');
        $timeLabel = $localized->format('g:i A');

        return (new MailMessage)
            ->subject(sprintf('Your onboarding call with %s is scheduled', $this->businessName))
            ->greeting('Your discovery call is scheduled.')
            ->line(sprintf('Business: %s', $this->businessName))
            ->line(sprintf('When: %s at %s (%s)', $dateLabel, $timeLabel, $this->timezone))
            ->action('Join Zoom Meeting', $this->joinUrl)
            ->line('If you need to reschedule, return to your onboarding link.');
    }
}
