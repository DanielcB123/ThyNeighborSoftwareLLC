<?php

declare(strict_types=1);

namespace Tests\Feature\Onboarding;

use App\Models\Central\OnboardingAppointment;
use App\Notifications\OnboardingMeetingScheduledNotification;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OnboardingMeetingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_start_and_schedule_an_onboarding_zoom_meeting(): void
    {
        $this->configureZoom();
        Notification::fake();
        Http::fake([
            'https://zoom.us/oauth/token' => Http::response([
                'access_token' => 'zoom-token',
                'expires_in' => 3600,
            ], 200),
            'https://api.zoom.us/v2/users/*/meetings' => Http::response([
                'id' => '91234567890',
                'join_url' => 'https://zoom.us/j/91234567890',
                'start_url' => 'https://zoom.us/s/91234567890?zak=secret',
                'password' => 'abc123',
                'status' => 'waiting',
            ], 201),
        ]);

        $startResponse = $this->get(route('onboarding.start'));
        $startResponse->assertRedirect();

        $redirectLocation = (string) $startResponse->headers->get('Location', '');
        parse_str((string) parse_url($redirectLocation, PHP_URL_QUERY), $query);
        $token = (string) ($query['token'] ?? '');

        $this->assertNotSame('', $token);

        /** @var OnboardingAppointment $appointment */
        $appointment = OnboardingAppointment::query()->firstOrFail();

        $scheduledAt = CarbonImmutable::now('America/New_York')->addDay()->setTime(14, 0);

        $scheduleResponse = $this->put(route('onboarding.schedule', $appointment), [
            'token' => $token,
            'business_name' => 'Smith Plumbing & HVAC',
            'contact_name' => 'Alex Client',
            'contact_email' => 'alex@example.com',
            'meeting_date' => $scheduledAt->format('Y-m-d'),
            'meeting_time' => $scheduledAt->format('H:i'),
            'timezone' => 'America/New_York',
            'duration_minutes' => 45,
        ]);

        $scheduleResponse->assertRedirect();

        $appointment->refresh();

        $this->assertSame('scheduled', $appointment->status);
        $this->assertSame('91234567890', $appointment->zoom_meeting_id);
        $this->assertSame('https://zoom.us/j/91234567890', $appointment->zoom_join_url);
        $this->assertSame('https://zoom.us/s/91234567890?zak=secret', $appointment->zoom_start_url);

        $this->get(route('onboarding.show', [
            'onboardingAppointment' => $appointment,
            'token' => $token,
        ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('appointment.zoomJoinUrl', 'https://zoom.us/j/91234567890')
                ->missing('appointment.zoomStartUrl')
                ->where('appointment.status', 'scheduled')
            );

        Notification::assertSentOnDemand(OnboardingMeetingScheduledNotification::class);
    }

    public function test_reschedule_updates_existing_zoom_meeting_instead_of_creating_new_meeting(): void
    {
        $this->configureZoom();
        Http::fake([
            'https://zoom.us/oauth/token' => Http::response([
                'access_token' => 'zoom-token',
                'expires_in' => 3600,
            ], 200),
            'https://api.zoom.us/v2/meetings/existing-meeting' => Http::sequence()
                ->push([], 204)
                ->push([
                    'id' => 'existing-meeting',
                    'join_url' => 'https://zoom.us/j/existing-meeting',
                    'start_url' => 'https://zoom.us/s/existing-meeting',
                    'password' => 'updated',
                    'status' => 'waiting',
                ], 200),
            'https://api.zoom.us/v2/users/*/meetings' => Http::response([], 500),
        ]);

        [$appointment, $token] = $this->createAppointmentWithToken([
            'status' => 'scheduled',
            'business_name' => 'Acme Services',
            'contact_name' => 'Pat Client',
            'contact_email' => 'pat@example.com',
            'scheduled_at' => CarbonImmutable::now()->addDay()->utc(),
            'timezone' => 'America/Chicago',
            'duration_minutes' => 45,
            'zoom_meeting_id' => 'existing-meeting',
            'zoom_join_url' => 'https://zoom.us/j/existing-meeting',
            'zoom_start_url' => 'https://zoom.us/s/existing-meeting',
            'zoom_status' => 'scheduled',
        ]);

        $rescheduledAt = CarbonImmutable::now('America/Chicago')->addDays(2)->setTime(9, 30);

        $this->put(route('onboarding.schedule', $appointment), [
            'token' => $token,
            'business_name' => 'Acme Services',
            'contact_name' => 'Pat Client',
            'contact_email' => 'pat@example.com',
            'meeting_date' => $rescheduledAt->format('Y-m-d'),
            'meeting_time' => $rescheduledAt->format('H:i'),
            'timezone' => 'America/Chicago',
            'duration_minutes' => 60,
        ])->assertRedirect();

        $appointment->refresh();

        $this->assertSame('existing-meeting', $appointment->zoom_meeting_id);
        $this->assertSame('https://zoom.us/j/existing-meeting', $appointment->zoom_join_url);
        $this->assertSame(60, $appointment->duration_minutes);

        Http::assertSent(fn (HttpRequest $request) => $request->method() === 'PATCH'
            && str_contains($request->url(), '/meetings/existing-meeting'));
        Http::assertNotSent(fn (HttpRequest $request) => $request->method() === 'POST'
            && str_contains($request->url(), '/users/'));
    }

    public function test_schedule_request_is_idempotent_for_same_meeting_payload(): void
    {
        $this->configureZoom();
        Http::fake();

        [$appointment, $token] = $this->createAppointmentWithToken([
            'status' => 'scheduled',
            'business_name' => 'Acme Services',
            'contact_name' => 'Pat Client',
            'contact_email' => 'pat@example.com',
            'scheduled_at' => CarbonImmutable::parse('2026-08-18 19:00:00', 'UTC'),
            'timezone' => 'America/New_York',
            'duration_minutes' => 45,
            'zoom_meeting_id' => 'existing-meeting',
            'zoom_join_url' => 'https://zoom.us/j/existing-meeting',
            'zoom_start_url' => 'https://zoom.us/s/existing-meeting',
            'zoom_status' => 'scheduled',
        ]);

        $this->put(route('onboarding.schedule', $appointment), [
            'token' => $token,
            'business_name' => 'Acme Services',
            'contact_name' => 'Pat Client',
            'contact_email' => 'pat@example.com',
            'meeting_date' => '2026-08-18',
            'meeting_time' => '15:00',
            'timezone' => 'America/New_York',
            'duration_minutes' => 45,
        ])->assertRedirect();

        Http::assertNothingSent();
    }

    public function test_scheduled_meeting_can_be_cancelled_and_zoom_meeting_is_deleted(): void
    {
        $this->configureZoom();
        Http::fake([
            'https://zoom.us/oauth/token' => Http::response([
                'access_token' => 'zoom-token',
                'expires_in' => 3600,
            ], 200),
            'https://api.zoom.us/v2/meetings/existing-meeting*' => Http::response([], 204),
        ]);

        [$appointment, $token] = $this->createAppointmentWithToken([
            'status' => 'scheduled',
            'business_name' => 'Acme Services',
            'contact_name' => 'Pat Client',
            'contact_email' => 'pat@example.com',
            'scheduled_at' => CarbonImmutable::now()->addDay()->utc(),
            'timezone' => 'America/Chicago',
            'duration_minutes' => 45,
            'zoom_meeting_id' => 'existing-meeting',
            'zoom_join_url' => 'https://zoom.us/j/existing-meeting',
            'zoom_start_url' => 'https://zoom.us/s/existing-meeting',
            'zoom_status' => 'scheduled',
        ]);

        $this->delete(route('onboarding.cancel', $appointment), [
            'token' => $token,
        ])->assertRedirect();

        $appointment->refresh();

        $this->assertSame('cancelled', $appointment->status);
        $this->assertSame('cancelled', $appointment->zoom_status);
        $this->assertNotNull($appointment->cancelled_at);

        Http::assertSent(fn (HttpRequest $request) => $request->method() === 'DELETE'
            && str_contains($request->url(), '/meetings/existing-meeting'));
    }

    private function configureZoom(): void
    {
        config()->set('services.zoom', [
            'account_id' => 'account-123',
            'client_id' => 'client-123',
            'client_secret' => 'secret-123',
            'host_user' => 'host@example.com',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array{0: OnboardingAppointment, 1: string}
     */
    private function createAppointmentWithToken(array $overrides = []): array
    {
        $token = OnboardingAppointment::issueAccessToken();

        $appointment = new OnboardingAppointment(array_merge([
            'business_name' => 'Default Business',
            'status' => 'pending',
            'duration_minutes' => 45,
        ], $overrides));
        $appointment->setAccessToken($token);
        $appointment->save();

        return [$appointment, $token];
    }
}
