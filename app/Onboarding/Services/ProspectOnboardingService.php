<?php

declare(strict_types=1);

namespace App\Onboarding\Services;

use App\Models\Central\OnboardingAppointment;
use App\Models\Central\OnboardingResponse;
use App\Models\Central\OnboardingResponseRevision;
use App\Models\Central\OnboardingSession;
use App\Models\Central\Prospect;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProspectOnboardingService
{
    /**
     * @var list<string>
     */
    public const STEP_KEYS = [
        'business',
        'project',
        'overview',
        'features',
        'assets',
        'branding',
        'timeline',
        'additional',
    ];

    /**
     * @var list<string>
     */
    private const REQUIRED_FOR_SCHEDULING = [
        'business',
        'project',
        'overview',
        'features',
        'assets',
        'branding',
        'timeline',
    ];

    /**
     * @var list<array{value: string, title: string, description: string}>
     */
    private const PROJECT_TYPES = [
        [
            'value' => 'new-website',
            'title' => 'Marketing / Business Website',
            'description' => 'A high-trust public website focused on messaging, conversion, and growth.',
        ],
        [
            'value' => 'ecommerce-website',
            'title' => 'E-Commerce Website',
            'description' => 'An online store with product management, checkout, and payment workflows.',
        ],
        [
            'value' => 'custom-web-application',
            'title' => 'Web Application',
            'description' => 'A custom application for customers, staff, or both.',
        ],
        [
            'value' => 'customer-portal',
            'title' => 'Customer Portal',
            'description' => 'An authenticated experience where customers can manage activity and information.',
        ],
        [
            'value' => 'internal-business-system',
            'title' => 'Internal Business Software',
            'description' => 'Operational software for workflows, approvals, and reporting.',
        ],
        [
            'value' => 'saas-product',
            'title' => 'SaaS Product',
            'description' => 'A multi-tenant software product customers subscribe to and use online.',
        ],
        [
            'value' => 'mobile-application',
            'title' => 'Mobile Application',
            'description' => 'A mobile-first product for iOS/Android users.',
        ],
        [
            'value' => 'website-redesign',
            'title' => 'Existing Website Redesign',
            'description' => 'A redesign of an existing site to improve clarity, trust, and conversion.',
        ],
        [
            'value' => 'software-upgrade',
            'title' => 'Existing Software Upgrade',
            'description' => 'Modernize or extend an existing software system.',
        ],
        [
            'value' => 'api-integration-work',
            'title' => 'API / Integration Work',
            'description' => 'Connect systems, automate data movement, and reduce manual operations.',
        ],
        [
            'value' => 'not-sure-yet',
            'title' => 'Not Sure Yet',
            'description' => 'You know something must improve but want help shaping the right direction.',
        ],
        [
            'value' => 'other',
            'title' => 'Other',
            'description' => 'A project direction not listed above.',
        ],
    ];

    /**
     * @var list<string>
     */
    private const FEATURE_OPTIONS = [
        'User Accounts / Login',
        'Admin Dashboard',
        'Customer Dashboard',
        'Payments',
        'Subscriptions',
        'E-Commerce',
        'Appointment Scheduling',
        'Messaging',
        'Notifications',
        'Email',
        'SMS',
        'File Uploads',
        'Document Management',
        'Search',
        'Reports / Analytics',
        'Maps / Location Features',
        'Third-Party API Integrations',
        'CRM Integration',
        'Accounting Integration',
        'Social Login',
        'Role / Permission Management',
        'Content Management',
        'Blog / News',
        'Forms / Lead Capture',
        'AI Features',
        'Mobile / Responsive Support',
    ];

    /**
     * @var list<string>
     */
    private const INDUSTRIES = [
        'Home services',
        'Construction',
        'Beauty and wellness',
        'Real estate',
        'Healthcare',
        'Professional services',
        'Retail',
        'Restaurants and hospitality',
        'Manufacturing',
        'Logistics',
        'Education',
        'Nonprofit',
        'Technology',
        'Enterprise operations',
        'Other',
    ];

    /**
     * @var list<string>
     */
    private const TIMELINE_OPTIONS = [
        'ASAP',
        'Within 1 month',
        '1–3 months',
        '3–6 months',
        '6+ months',
        'No fixed deadline',
        'Not sure',
    ];

    /**
     * @var list<string>
     */
    private const URGENCY_OPTIONS = [
        'Critical',
        'High',
        'Moderate',
        'Low',
    ];

    /**
     * @var list<string>
     */
    private const BUDGET_OPTIONS = [
        'Not sure yet',
        'Under $10k',
        '$10k - $25k',
        '$25k - $50k',
        '$50k - $100k',
        '$100k+',
        'Need guidance first',
    ];

    /**
     * @var list<string>
     */
    private const ASSET_OPTIONS = [
        'Existing website',
        'Owned domain',
        'Hosting environment',
        'Existing application or source code',
        'Existing customer/business data',
        'Current software to integrate with',
    ];

    public function startOrResumeSession(
        ?string $accessToken,
        ?string $ipAddress,
        ?string $userAgent
    ): OnboardingSession {
        if (is_string($accessToken) && $this->isValidSessionToken($accessToken)) {
            $existing = OnboardingSession::query()
                ->where('access_token', trim($accessToken))
                ->first();

            if ($existing !== null) {
                return $existing;
            }
        }

        if (is_string($ipAddress) && is_string($userAgent) && trim($userAgent) !== '') {
            $existingFingerprintSession = OnboardingSession::query()
                ->where('last_activity_ip', $ipAddress)
                ->where('last_activity_user_agent', $userAgent)
                ->where('status', '!=', 'intake_completed')
                ->latest('updated_at')
                ->first();

            if ($existingFingerprintSession !== null) {
                return $existingFingerprintSession;
            }
        }

        return DB::transaction(function () use ($ipAddress, $userAgent): OnboardingSession {
            $prospect = Prospect::query()->create([
                'intake_status' => 'in_progress',
            ]);

            $session = OnboardingSession::query()->create([
                'prospect_id' => $prospect->id,
                'access_token' => Str::random(64),
                'status' => 'draft',
                'current_step' => 1,
                'last_activity_ip' => $ipAddress,
                'last_activity_user_agent' => $userAgent,
            ]);

            return $session;
        });
    }

    public function requireSessionByToken(string $accessToken): OnboardingSession
    {
        if (! $this->isValidSessionToken($accessToken)) {
            throw new ModelNotFoundException('Onboarding session was not found.');
        }

        $session = OnboardingSession::query()
            ->where('access_token', $accessToken)
            ->first();

        if ($session === null) {
            throw new ModelNotFoundException('Onboarding session was not found.');
        }

        return $session;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function saveStep(
        OnboardingSession $session,
        string $stepKey,
        array $payload,
        ?string $ipAddress,
        ?string $userAgent,
    ): OnboardingSession {
        $normalizedStepKey = trim($stepKey);

        if (! in_array($normalizedStepKey, self::STEP_KEYS, true)) {
            throw new \InvalidArgumentException('Unknown onboarding step key provided.');
        }

        DB::transaction(function () use (
            $session,
            $normalizedStepKey,
            $payload,
            $ipAddress,
            $userAgent
        ): void {
            $responseId = OnboardingResponse::query()
                ->where('onboarding_session_id', $session->id)
                ->where('step_key', $normalizedStepKey)
                ->value('id');

            if ($responseId !== null) {
                $responseId = (int) $responseId;
                OnboardingResponse::query()
                    ->where('id', $responseId)
                    ->update([
                        'response_payload' => json_encode($payload, JSON_THROW_ON_ERROR),
                        'completed_at' => now(),
                        'updated_at' => now(),
                    ]);
            } else {
                /** @var int $createdResponseId */
                $createdResponseId = (int) OnboardingResponse::query()->insertGetId([
                    'onboarding_session_id' => $session->id,
                    'step_key' => $normalizedStepKey,
                    'response_payload' => json_encode($payload, JSON_THROW_ON_ERROR),
                    'completed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $responseId = $createdResponseId;
            }

            $nextRevision = ((int) OnboardingResponseRevision::query()
                ->where('onboarding_response_id', $responseId)
                ->max('revision_number')) + 1;

            OnboardingResponseRevision::query()->create([
                'onboarding_response_id' => $responseId,
                'revision_number' => $nextRevision,
                'response_payload' => $payload,
                'changed_at' => now(),
            ]);

            $stepNumber = $this->stepNumberForKey($normalizedStepKey);

            $session->forceFill([
                'current_step' => max($session->current_step, $stepNumber),
                'last_saved_at' => now(),
                'last_activity_ip' => $ipAddress,
                'last_activity_user_agent' => $userAgent,
            ])->save();

            $this->syncProspectFromStep($session, $normalizedStepKey, $payload);
        });

        return $session->fresh() ?? $session;
    }

    /**
     * @return array{
     *   stepKeys: list<string>,
     *   projectTypes: list<array{value: string, title: string, description: string}>,
     *   industries: list<string>,
     *   featureOptions: list<string>,
     *   assetOptions: list<string>,
     *   timelineOptions: list<string>,
     *   urgencyOptions: list<string>,
     *   budgetOptions: list<string>
     * }
     */
    public function onboardingOptions(): array
    {
        return [
            'stepKeys' => self::STEP_KEYS,
            'projectTypes' => self::PROJECT_TYPES,
            'industries' => self::INDUSTRIES,
            'featureOptions' => self::FEATURE_OPTIONS,
            'assetOptions' => self::ASSET_OPTIONS,
            'timelineOptions' => self::TIMELINE_OPTIONS,
            'urgencyOptions' => self::URGENCY_OPTIONS,
            'budgetOptions' => self::BUDGET_OPTIONS,
        ];
    }

    public function canContinueToScheduling(OnboardingSession $session): bool
    {
        $completedSteps = $this->completedStepKeysQuery($session);

        foreach (self::REQUIRED_FOR_SCHEDULING as $requiredStep) {
            if (! in_array($requiredStep, $completedSteps, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws ValidationException
     */
    public function markIntakeCompleted(OnboardingSession $session): OnboardingSession
    {
        if (! $this->canContinueToScheduling($session)) {
            throw ValidationException::withMessages([
                'stepKey' => ['Complete the required onboarding sections before continuing.'],
            ]);
        }

        $session->forceFill([
            'status' => 'intake_completed',
            'completed_at' => now(),
            'last_saved_at' => now(),
        ])->save();

        $session->prospect?->forceFill([
            'intake_status' => 'intake_completed',
            'completed_at' => now(),
        ])->save();

        return $session->fresh() ?? $session;
    }

    /**
     * @return array{appointment: OnboardingAppointment, token: string}
     */
    public function ensureAppointmentForSession(OnboardingSession $session): array
    {
        $session->loadMissing(['prospect', 'onboardingAppointment']);
        $responseMap = $this->responseMap($session);

        $businessPayload = Arr::wrap($responseMap['business'] ?? []);
        $projectPayload = Arr::wrap($responseMap['project'] ?? []);

        $businessName = trim((string) Arr::get($businessPayload, 'businessName', ''));
        $contactName = trim((string) Arr::get($businessPayload, 'contactName', ''));
        $contactEmail = trim((string) Arr::get($businessPayload, 'businessEmail', ''));

        if ($businessName === '') {
            $businessName = trim((string) ($session->prospect?->business_name ?? 'Discovery Client'));
        }

        $projectTypes = Arr::wrap(Arr::get($projectPayload, 'projectTypes', []));
        $projectSummary = trim((string) Arr::get($projectPayload, 'projectSummary', ''));
        $projectDirection = $projectSummary !== ''
            ? $projectSummary
            : implode(', ', array_values(array_filter(array_map('strval', $projectTypes))));

        /** @var OnboardingAppointment $appointment */
        $appointment = $session->onboardingAppointment ?? new OnboardingAppointment([
            'status' => 'pending',
            'duration_minutes' => 45,
        ]);

        $appointment->fill([
            'business_name' => $businessName !== '' ? $businessName : 'Discovery Client',
            'contact_name' => $contactName !== '' ? $contactName : null,
            'contact_email' => $contactEmail !== '' ? $contactEmail : null,
            'tenant_public_id' => $session->public_id,
        ]);
        $appointment->save();

        if ($session->onboarding_appointment_id !== $appointment->id) {
            $session->forceFill([
                'onboarding_appointment_id' => $appointment->id,
            ])->save();
        }

        if ($projectDirection !== '') {
            $session->prospect?->forceFill([
                'project_direction' => $projectDirection,
            ])->save();
        }

        $token = OnboardingAppointment::issueAccessToken();
        $appointment->setAccessToken($token);
        $appointment->save();

        return [
            'appointment' => $appointment->fresh() ?? $appointment,
            'token' => $token,
        ];
    }

    /**
     * @return array{
     *   business: array<string, mixed>,
     *   project: array<string, mixed>,
     *   overview: array<string, mixed>,
     *   features: array<string, mixed>,
     *   assets: array<string, mixed>,
     *   branding: array<string, mixed>,
     *   timeline: array<string, mixed>,
     *   additional: array<string, mixed>
     * }
     */
    public function intakeSummary(OnboardingSession $session): array
    {
        $responseMap = $this->responseMap($session);

        return [
            'business' => Arr::wrap($responseMap['business'] ?? []),
            'project' => Arr::wrap($responseMap['project'] ?? []),
            'overview' => Arr::wrap($responseMap['overview'] ?? []),
            'features' => Arr::wrap($responseMap['features'] ?? []),
            'assets' => Arr::wrap($responseMap['assets'] ?? []),
            'branding' => Arr::wrap($responseMap['branding'] ?? []),
            'timeline' => Arr::wrap($responseMap['timeline'] ?? []),
            'additional' => Arr::wrap($responseMap['additional'] ?? []),
        ];
    }

    /**
     * @return array{
     *   sessionToken: string,
     *   workspacePublicId: string,
     *   resumeUrl: string,
     *   status: string,
     *   currentStep: int,
     *   lastSavedAt: string|null,
     *   responses: array<string, array<string, mixed>>,
     *   capabilities: array{
     *     canEdit: bool,
     *     canContinueToScheduling: bool,
     *     requiredSteps: list<string>,
     *     completedSteps: list<string>
     *   }
     * }
     */
    public function sessionSnapshot(OnboardingSession $session, bool $includeResponses = true): array
    {
        $responseMap = $includeResponses ? $this->responseMap($session) : [];
        $completedSteps = $includeResponses
            ? $this->completedStepKeysFromMap($responseMap)
            : $this->completedStepKeysQuery($session);

        return [
            'sessionToken' => (string) $session->access_token,
            'workspacePublicId' => (string) $session->public_id,
            'resumeUrl' => url('/start-project?session='.$session->access_token),
            'status' => (string) $session->status,
            'currentStep' => (int) $session->current_step,
            'lastSavedAt' => $session->last_saved_at?->toIso8601String(),
            'responses' => $responseMap,
            'capabilities' => [
                'canEdit' => true,
                'canContinueToScheduling' => $this->canContinueToScheduling($session),
                'requiredSteps' => self::REQUIRED_FOR_SCHEDULING,
                'completedSteps' => $completedSteps,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function syncProspectFromStep(
        OnboardingSession $session,
        string $stepKey,
        array $payload
    ): void {
        $session->loadMissing('prospect');

        /** @var Prospect $prospect */
        $prospect = $session->prospect;
        if (! $prospect instanceof Prospect) {
            return;
        }

        if ($stepKey === 'project') {
            $projectTypes = Arr::wrap(Arr::get($payload, 'projectTypes', []));

            $prospect->forceFill([
                'project_direction' => implode(', ', array_values(array_filter(array_map('strval', $projectTypes)))),
            ])->save();

            return;
        }

        if ($stepKey !== 'business') {
            return;
        }

        $prospect->forceFill([
            'primary_contact_name' => Arr::get($payload, 'contactName'),
            'primary_contact_role' => Arr::get($payload, 'contactRole'),
            'business_name' => Arr::get($payload, 'businessName'),
            'business_email' => Arr::get($payload, 'businessEmail'),
            'business_phone' => Arr::get($payload, 'phone'),
            'industry' => Arr::get($payload, 'industry'),
            'industry_other' => Arr::get($payload, 'industryOther', Arr::get($payload, 'businessType')),
            'business_location' => Arr::get($payload, 'businessLocation', Arr::get($payload, 'location')),
            'location_count' => Arr::get($payload, 'locationCount', 1),
            'team_size' => Arr::get($payload, 'teamSize', Arr::get($payload, 'employeeCount')),
        ])->save();

        $prospect->contacts()->delete();

        $prospect->contacts()->create([
            'name' => Arr::get($payload, 'contactName'),
            'email' => Arr::get($payload, 'businessEmail'),
            'phone' => Arr::get($payload, 'phone'),
            'role' => Arr::get($payload, 'contactRole'),
            'is_primary' => true,
            'invite_later' => false,
        ]);
    }

    private function stepNumberForKey(string $stepKey): int
    {
        $position = array_search($stepKey, self::STEP_KEYS, true);

        if ($position === false) {
            return 1;
        }

        return $position + 1;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function responseMap(OnboardingSession $session): array
    {
        $responseMap = [];

        foreach (self::STEP_KEYS as $stepKey) {
            $response = OnboardingResponse::query()
                ->where('onboarding_session_id', $session->id)
                ->where('step_key', $stepKey)
                ->orderByDesc('id')
                ->first(['id', 'step_key', 'response_payload']);

            if (! $response instanceof OnboardingResponse) {
                continue;
            }

            $payload = $response->response_payload;
            $responseMap[$stepKey] = is_array($payload) ? $payload : [];
        }

        return $responseMap;
    }

    /**
     * @return list<string>
     */
    private function completedStepKeysQuery(OnboardingSession $session): array
    {
        /** @var Collection<int, string> $stepKeys */
        $stepKeys = OnboardingResponse::query()
            ->where('onboarding_session_id', $session->id)
            ->whereIn('step_key', self::STEP_KEYS)
            ->whereRaw('JSON_LENGTH(response_payload) > 0')
            ->pluck('step_key');

        return $stepKeys
            ->map(static fn (mixed $stepKey): string => (string) $stepKey)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, array<string, mixed>>  $responseMap
     * @return list<string>
     */
    private function completedStepKeysFromMap(array $responseMap): array
    {
        return collect($responseMap)
            ->filter(static fn (mixed $payload): bool => is_array($payload) && $payload !== [])
            ->keys()
            ->map(static fn (mixed $key): string => (string) $key)
            ->values()
            ->all();
    }

    private function isValidSessionToken(string $accessToken): bool
    {
        return preg_match('/^[A-Za-z0-9]{64}$/', trim($accessToken)) === 1;
    }
}
