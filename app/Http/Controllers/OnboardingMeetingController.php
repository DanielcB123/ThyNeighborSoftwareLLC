<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CancelOnboardingMeetingRequest;
use App\Http\Requests\ScheduleOnboardingMeetingRequest;
use App\Models\Central\OnboardingAppointment;
use App\Services\Onboarding\Data\OnboardingMeetingScheduleData;
use App\Services\Onboarding\OnboardingMeetingScheduler;
use App\Services\Zoom\Exceptions\ZoomIntegrationException;
use DateTimeZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class OnboardingMeetingController extends Controller
{
    public function start(Request $request): RedirectResponse
    {
        $appointmentPublicId = (string) $request->session()->get('onboarding.appointment_public_id', '');
        $token = (string) $request->session()->get('onboarding.appointment_token', '');

        if ($appointmentPublicId !== '' && $token !== '') {
            /** @var OnboardingAppointment|null $existing */
            $existing = OnboardingAppointment::query()
                ->where('public_id', '=', $appointmentPublicId)
                ->first();

            if ($existing instanceof OnboardingAppointment && $existing->matchesAccessToken($token)) {
                return redirect()->route('onboarding.show', [
                    'onboardingAppointment' => $existing,
                    'token' => $token,
                ]);
            }
        }

        $newToken = OnboardingAppointment::issueAccessToken();

        $appointment = new OnboardingAppointment([
            'tenant_public_id' => (string) data_get($request->attributes->get('frontendTenantContext'), 'publicId') ?: null,
            'business_name' => (string) data_get($request->attributes->get('frontendTenantContext'), 'displayName', 'Discovery Client'),
            'status' => 'pending',
            'duration_minutes' => 45,
        ]);
        $appointment->setAccessToken($newToken);
        $appointment->save();

        $request->session()->put('onboarding.appointment_public_id', $appointment->public_id);
        $request->session()->put('onboarding.appointment_token', $newToken);

        return redirect()->route('onboarding.show', [
            'onboardingAppointment' => $appointment,
            'token' => $newToken,
        ]);
    }

    public function show(Request $request, OnboardingAppointment $onboardingAppointment): Response
    {
        $this->ensureAuthorizedAccess($request, $onboardingAppointment);

        return Inertia::render('Onboarding/ScheduleMeeting', [
            'appointment' => $this->presentAppointment($onboardingAppointment),
            'token' => $this->accessToken($request),
            'timezoneOptions' => DateTimeZone::listIdentifiers(),
            'status' => session('status'),
        ]);
    }

    public function schedule(
        ScheduleOnboardingMeetingRequest $request,
        OnboardingAppointment $onboardingAppointment,
        OnboardingMeetingScheduler $onboardingMeetingScheduler,
    ): RedirectResponse {
        $validated = $request->validated();

        try {
            $onboardingMeetingScheduler->schedule(
                $onboardingAppointment,
                new OnboardingMeetingScheduleData(
                    businessName: (string) $validated['business_name'],
                    contactName: (string) $validated['contact_name'],
                    contactEmail: (string) $validated['contact_email'],
                    meetingDate: (string) $validated['meeting_date'],
                    meetingTime: (string) $validated['meeting_time'],
                    timezone: (string) $validated['timezone'],
                    durationMinutes: (int) $validated['duration_minutes'],
                ),
            );
        } catch (ZoomIntegrationException $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'meeting' => $exception->userMessage(),
                ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'meeting' => 'We could not save your meeting right now. Please try again.',
                ]);
        }

        return redirect()->route('onboarding.show', [
            'onboardingAppointment' => $onboardingAppointment,
            'token' => $this->accessToken($request),
        ])->with('status', 'scheduled');
    }

    public function cancel(
        CancelOnboardingMeetingRequest $request,
        OnboardingAppointment $onboardingAppointment,
        OnboardingMeetingScheduler $onboardingMeetingScheduler,
    ): RedirectResponse {
        try {
            $onboardingMeetingScheduler->cancel($onboardingAppointment);
        } catch (ZoomIntegrationException $exception) {
            report($exception);

            return back()->withErrors([
                'meeting' => $exception->userMessage(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'meeting' => 'We could not cancel your meeting right now. Please try again.',
            ]);
        }

        return redirect()->route('onboarding.show', [
            'onboardingAppointment' => $onboardingAppointment,
            'token' => $this->accessToken($request),
        ])->with('status', 'cancelled');
    }

    private function ensureAuthorizedAccess(Request $request, OnboardingAppointment $appointment): void
    {
        if ($request->user() !== null) {
            return;
        }

        abort_unless($appointment->matchesAccessToken($this->accessToken($request)), 403);
    }

    private function accessToken(Request $request): string
    {
        return (string) $request->input('token', $request->query('token', ''));
    }

    /**
     * @return array<string, mixed>
     */
    private function presentAppointment(OnboardingAppointment $appointment): array
    {
        return [
            'publicId' => (string) $appointment->public_id,
            'businessName' => (string) $appointment->business_name,
            'contactName' => (string) ($appointment->contact_name ?? ''),
            'contactEmail' => (string) ($appointment->contact_email ?? ''),
            'status' => (string) $appointment->status,
            'scheduledAt' => $appointment->scheduled_at?->toIso8601String(),
            'timezone' => (string) ($appointment->timezone ?? config('app.timezone', 'UTC')),
            'durationMinutes' => (int) $appointment->duration_minutes,
            'zoomJoinUrl' => $appointment->status === 'scheduled' ? $appointment->zoom_join_url : null,
            'zoomPasscode' => $appointment->status === 'scheduled' ? $appointment->zoom_passcode : null,
            'zoomStatus' => (string) ($appointment->zoom_status ?? ''),
        ];
    }
}
