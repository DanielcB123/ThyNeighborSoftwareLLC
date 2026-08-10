<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\ScheduleDiscoveryMeetingRequest;
use App\Onboarding\Services\ProspectOnboardingService;
use Illuminate\Http\JsonResponse;

class ScheduleDiscoveryMeetingController extends Controller
{
    public function __invoke(
        ScheduleDiscoveryMeetingRequest $request,
        ProspectOnboardingService $prospectOnboardingService
    ): JsonResponse {
        $sessionToken = (string) $request->string('sessionToken');

        $session = $prospectOnboardingService->requireSessionByToken($sessionToken);
        $meeting = $prospectOnboardingService->scheduleMeeting(
            $session,
            $request->array('payload'),
            $request->ip(),
            $request->userAgent(),
        );

        $updatedSession = $prospectOnboardingService->requireSessionByToken($sessionToken);

        return response()->json([
            'meetingPublicId' => $meeting->public_id,
            'session' => $prospectOnboardingService->sessionSnapshot($updatedSession),
        ]);
    }
}
