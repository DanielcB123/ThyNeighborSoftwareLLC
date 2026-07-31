<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\UploadOnboardingMaterialsRequest;
use App\Onboarding\Services\ProspectOnboardingService;
use Illuminate\Http\JsonResponse;

class UploadOnboardingMaterialsController extends Controller
{
    public function __invoke(
        UploadOnboardingMaterialsRequest $request,
        ProspectOnboardingService $prospectOnboardingService
    ): JsonResponse {
        $session = $prospectOnboardingService->requireSessionByToken(
            (string) $request->string('sessionToken')
        );

        $uploadedMaterials = $prospectOnboardingService->appendUploadedMaterials(
            $session,
            $request->file('materials', []),
            $request->ip(),
            $request->userAgent(),
        );

        $updatedSession = $prospectOnboardingService->requireSessionByToken(
            (string) $request->string('sessionToken')
        );

        return response()->json([
            'uploadedMaterials' => $uploadedMaterials,
            'session' => $prospectOnboardingService->sessionSnapshot($updatedSession),
        ]);
    }
}
