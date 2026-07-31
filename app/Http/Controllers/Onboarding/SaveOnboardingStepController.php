<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\OnboardingStepSaveRequest;
use App\Onboarding\Services\ProspectOnboardingService;
use Illuminate\Http\JsonResponse;

class SaveOnboardingStepController extends Controller
{
    public function __invoke(
        OnboardingStepSaveRequest $request,
        ProspectOnboardingService $prospectOnboardingService
    ): JsonResponse {
        $session = $prospectOnboardingService->requireSessionByToken(
            (string) $request->string('sessionToken')
        );

        $updatedSession = $prospectOnboardingService->saveStep(
            $session,
            (string) $request->string('stepKey'),
            $request->array('payload'),
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json([
            'saved' => true,
            'session' => $prospectOnboardingService->sessionSnapshot($updatedSession),
        ]);
    }
}
