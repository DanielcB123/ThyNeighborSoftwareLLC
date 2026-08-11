<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\CompleteOnboardingIntakeRequest;
use App\Onboarding\Services\ProspectOnboardingService;
use Illuminate\Http\JsonResponse;

class CompleteOnboardingIntakeController extends Controller
{
    public function __invoke(
        CompleteOnboardingIntakeRequest $request,
        ProspectOnboardingService $prospectOnboardingService
    ): JsonResponse {
        $sessionToken = (string) $request->string('sessionToken');
        $session = $prospectOnboardingService->requireSessionByToken($sessionToken);
        $prospectOnboardingService->markIntakeCompleted($session);

        return response()->json([
            'completed' => true,
            'redirectUrl' => route('onboarding.start', ['discovery_session' => $sessionToken]),
        ]);
    }
}
