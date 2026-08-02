<?php

declare(strict_types=1);

namespace App\Onboarding\Services;

use App\Enums\Onboarding\DiscoverySessionStatus;
use App\Enums\Onboarding\InquirySpamDisposition;
use App\Enums\Onboarding\InquirySubmissionStatus;
use App\Enums\Onboarding\LeadPriority;
use App\Enums\Onboarding\LeadStatus;
use App\Enums\Onboarding\ProspectTaskStatus;
use App\Enums\Onboarding\ProspectTaskType;
use App\Enums\Onboarding\ProspectWorkspaceAccessScope;
use App\Enums\Onboarding\ProspectWorkspaceMemberRole;
use App\Enums\Onboarding\ProspectWorkspaceMemberStatus;
use App\Enums\Onboarding\ProspectWorkspaceStatus;
use App\Models\Central\DiscoveryMeeting;
use App\Models\Central\InquirySubmission;
use App\Models\Central\LeadProjectInterest;
use App\Models\Central\LeadSource;
use App\Models\Central\OnboardingResponse;
use App\Models\Central\OnboardingSession;
use App\Models\Central\Prospect;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProspectOnboardingService
{
    /**
     * @var list<string>
     */
    public const STEP_KEYS = ['project', 'business', 'goals', 'preparation', 'meeting'];

    /**
     * @var list<array{value: string, title: string, description: string}>
     */
    private const PROJECT_DIRECTIONS = [
        [
            'value' => 'new-website',
            'title' => 'A new website',
            'description' => 'A new marketing website that presents your business clearly and converts qualified leads.',
        ],
        [
            'value' => 'website-redesign',
            'title' => 'A website redesign',
            'description' => 'Refresh an existing website that no longer reflects your quality, goals, or customer expectations.',
        ],
        [
            'value' => 'website-business-features',
            'title' => 'A website with business features',
            'description' => 'A public website connected to scheduling, payments, records, dashboards, portals, or automation.',
        ],
        [
            'value' => 'custom-web-application',
            'title' => 'A custom web application',
            'description' => 'A purpose-built browser-based product for customers, staff, or both.',
        ],
        [
            'value' => 'mobile-application',
            'title' => 'A mobile application',
            'description' => 'A mobile-first product for iOS/Android users, teams, or partner organizations.',
        ],
        [
            'value' => 'internal-business-system',
            'title' => 'An internal business system',
            'description' => 'Operational software for workflows like dispatch, service coordination, approvals, and reporting.',
        ],
        [
            'value' => 'multi-location-enterprise-platform',
            'title' => 'A multi-location or enterprise platform',
            'description' => 'A shared platform with controlled variation across regions, branches, departments, or teams.',
        ],
        [
            'value' => 'not-sure-yet',
            'title' => 'I am not sure yet',
            'description' => 'You know you need improvement and want expert guidance before committing to one direction.',
        ],
    ];

    /**
     * @var list<string>
     */
    private const BUSINESS_ROLES = [
        'Owner',
        'Founder',
        'Executive',
        'Manager',
        'Marketing',
        'Operations',
        'Technology',
        'Employee',
        'Consultant',
        'Other',
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
    private const GOAL_OPTIONS = [
        'Bring in more customers',
        'Look more professional',
        'Replace an outdated website',
        'Make it easier for customers to contact us',
        'Sell products or services online',
        'Allow customers to schedule',
        'Allow customers to pay',
        'Create a customer portal',
        'Replace spreadsheets or manual work',
        'Improve reporting',
        'Connect multiple locations',
        'Build a completely new product',
        'Improve an existing application',
        'Something else',
    ];

    /**
     * @var list<string>
     */
    private const TIMELINE_COMFORT_OPTIONS = [
        'As soon as practical',
        'Within 1 to 2 months',
        'Within 3 to 6 months',
        'Within 6 to 12 months',
        'Still exploring timing',
    ];

    /**
     * @var list<string>
     */
    private const BUDGET_COMFORT_OPTIONS = [
        'Need guidance first',
        'Budget-conscious but flexible for ROI',
        'Ready for a meaningful investment',
        'Large initiative with multiple phases',
    ];

    /**
     * @var list<string>
     */
    private const MEETING_FORMAT_OPTIONS = ['video', 'phone', 'in-person'];

    /**
     * @var list<string>
     */
    private const ASSET_CHECKLIST = [
        'Current website URL',
        'Brand files or style guide',
        'Photos, videos, or marketing collateral',
        'Existing process documents or SOPs',
        'Current software screenshots',
        'Sample reports or spreadsheets',
    ];

    public function startOrResumeSession(
        ?string $accessToken,
        ?string $ipAddress,
        ?string $userAgent
    ): OnboardingSession {
        if (is_string($accessToken) && trim($accessToken) !== '') {
            $existing = OnboardingSession::query()
                ->where('access_token', trim($accessToken))
                ->with(['prospect.contacts', 'responses', 'discoveryMeeting', 'workspace.members'])
                ->first();

            if ($existing !== null) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($ipAddress, $userAgent): OnboardingSession {
            $source = LeadSource::query()->firstOrCreate(
                ['slug' => 'website-start-project'],
                [
                    'name' => 'Website start-project form',
                    'status' => 'active',
                    'description' => 'Primary public inquiry intake through /start-project.',
                ]
            );

            $inquiry = InquirySubmission::query()->create([
                'lead_source_id' => $source->id,
                'status' => InquirySubmissionStatus::Submitted->value,
                'spam_disposition' => InquirySpamDisposition::NotChecked->value,
                'context' => ['entryPoint' => '/start-project'],
                'submitted_at' => now(),
            ]);

            $prospect = Prospect::query()->create([
                'inquiry_submission_id' => $inquiry->id,
                'status' => LeadStatus::New->value,
                'priority' => LeadPriority::Normal->value,
            ]);

            $workspace = $prospect->activeWorkspace()->create([
                'status' => ProspectWorkspaceStatus::Active->value,
                'access_scope' => ProspectWorkspaceAccessScope::InvitedOnly->value,
                'active_workspace_key' => 1,
                'workspace_name' => 'Project Discovery Workspace',
                'opened_at' => now(),
            ]);

            $session = OnboardingSession::query()->create([
                'lead_id' => $prospect->id,
                'prospect_workspace_id' => $workspace->id,
                'access_token' => Str::random(64),
                'status' => DiscoverySessionStatus::InProgress->value,
                'current_step' => 1,
                'last_activity_ip' => $ipAddress,
                'last_activity_user_agent' => $userAgent,
            ]);

            return $session->load(['prospect.contacts', 'responses', 'discoveryMeeting', 'workspace.members']);
        });
    }

    public function requireSessionByToken(string $accessToken): OnboardingSession
    {
        $session = OnboardingSession::query()
            ->where('access_token', $accessToken)
            ->with(['prospect.contacts', 'responses', 'discoveryMeeting', 'workspace.members'])
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

        $session->loadMissing(['prospect.contacts', 'responses', 'discoveryMeeting', 'workspace.members']);

        DB::transaction(function () use (
            $session,
            $normalizedStepKey,
            $payload,
            $ipAddress,
            $userAgent
        ): void {
            $response = $session->responses()->updateOrCreate(
                [
                    'question_key' => $normalizedStepKey,
                    'current_response_key' => 1,
                ],
                [
                    'response_payload' => $payload,
                    'completed_at' => now(),
                ]
            );

            $revisionNumber = (int) DB::table('discovery_response_revisions')
                ->where('discovery_response_id', $response->id)
                ->max('revision_number');

            DB::table('discovery_response_revisions')->insert([
                'public_id' => (string) Str::ulid(),
                'discovery_response_id' => $response->id,
                'revision_number' => $revisionNumber + 1,
                'response_payload' => json_encode($payload, JSON_THROW_ON_ERROR),
                'changed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
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

        return $session->fresh(['prospect.contacts', 'responses', 'discoveryMeeting', 'workspace.members']);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function scheduleMeeting(
        OnboardingSession $session,
        array $payload,
        ?string $ipAddress,
        ?string $userAgent,
    ): DiscoveryMeeting {
        $session = $this->saveStep($session, 'meeting', $payload, $ipAddress, $userAgent);

        $meeting = DB::transaction(function () use ($session, $payload): DiscoveryMeeting {
            /** @var Prospect $prospect */
            $prospect = $session->prospect;

            $meeting = DiscoveryMeeting::query()->updateOrCreate(
                ['discovery_session_id' => $session->id],
                [
                    'prospect_workspace_id' => $session->prospect_workspace_id,
                    'lead_id' => $prospect->id,
                    'task_type' => ProspectTaskType::DiscoveryMeeting->value,
                    'status' => ProspectTaskStatus::Requested->value,
                    'title' => 'Discovery meeting requested',
                    'description' => 'Prospect requested scheduling from onboarding flow.',
                    'meeting_format' => (string) Arr::get($payload, 'meetingFormat', 'video'),
                    'timezone' => (string) Arr::get($payload, 'timezone', 'UTC'),
                    'preferred_start_date' => Arr::get($payload, 'preferredStartDate'),
                    'preferred_end_date' => Arr::get($payload, 'preferredEndDate'),
                    'availability_notes' => Arr::get($payload, 'availabilityNotes'),
                    'duration_minutes' => 45,
                    'task_payload' => [
                        'attendees' => Arr::wrap(Arr::get($payload, 'attendees', [])),
                    ],
                ]
            );

            $session->forceFill([
                'status' => DiscoverySessionStatus::DiscoveryComplete->value,
                'current_step' => 5,
                'last_saved_at' => now(),
                'completed_at' => now(),
            ])->save();

            $prospect->forceFill([
                'status' => LeadStatus::DiscoveryComplete->value,
                'discovery_completed_at' => now(),
            ])->save();

            if ($session->workspace !== null) {
                $session->workspace->update([
                    'status' => ProspectWorkspaceStatus::DiscoveryComplete->value,
                    'discovery_completed_at' => now(),
                ]);
            }

            return $meeting;
        });

        $session->forceFill([
            'last_activity_ip' => $ipAddress,
            'last_activity_user_agent' => $userAgent,
        ])->save();

        return $meeting->fresh();
    }

    /**
     * @param  array<int, UploadedFile>  $materials
     * @return list<array{originalName: string, storagePath: string, mimeType: string, size: int, uploadedAt: string}>
     */
    public function appendUploadedMaterials(
        OnboardingSession $session,
        array $materials,
        ?string $ipAddress,
        ?string $userAgent,
    ): array {
        $uploaded = [];
        $folder = sprintf('onboarding-materials/%s', $session->public_id);

        foreach ($materials as $material) {
            $storedPath = $material->store($folder);
            $uploaded[] = [
                'originalName' => (string) $material->getClientOriginalName(),
                'storagePath' => (string) $storedPath,
                'mimeType' => (string) $material->getClientMimeType(),
                'size' => (int) $material->getSize(),
                'uploadedAt' => now()->toIso8601String(),
            ];
        }

        $preparationResponse = $session->responses()
            ->where('question_key', 'preparation')
            ->first();

        /** @var array<string, mixed> $payload */
        $payload = is_array($preparationResponse?->response_payload)
            ? $preparationResponse->response_payload
            : [];

        $existingMaterials = collect(
            Arr::wrap(Arr::get($payload, 'uploadedMaterials', []))
        )->map(static fn (mixed $material): array => is_array($material) ? $material : [])
            ->filter(static fn (array $material): bool => $material !== [])
            ->values();

        $mergedMaterials = $existingMaterials
            ->concat($uploaded)
            ->unique(static fn (array $material): string => (string) Arr::get($material, 'storagePath'))
            ->values()
            ->all();

        $payload['uploadedMaterials'] = $mergedMaterials;

        $this->saveStep($session, 'preparation', $payload, $ipAddress, $userAgent);

        return $uploaded;
    }

    /**
     * @return array{
     *   stepKeys: list<string>,
     *   projectDirections: list<array{value: string, title: string, description: string}>,
     *   businessRoles: list<string>,
     *   industries: list<string>,
     *   goals: list<string>,
     *   timelineComfort: list<string>,
     *   budgetComfort: list<string>,
     *   meetingFormats: list<string>,
     *   assetChecklist: list<string>
     * }
     */
    public function onboardingOptions(): array
    {
        return [
            'stepKeys' => self::STEP_KEYS,
            'projectDirections' => self::PROJECT_DIRECTIONS,
            'businessRoles' => self::BUSINESS_ROLES,
            'industries' => self::INDUSTRIES,
            'goals' => self::GOAL_OPTIONS,
            'timelineComfort' => self::TIMELINE_COMFORT_OPTIONS,
            'budgetComfort' => self::BUDGET_COMFORT_OPTIONS,
            'meetingFormats' => self::MEETING_FORMAT_OPTIONS,
            'assetChecklist' => self::ASSET_CHECKLIST,
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
     *   responses: array<string, array<string, mixed>>
     * }
     */
    public function sessionSnapshot(OnboardingSession $session): array
    {
        $session->loadMissing(['responses']);

        /** @var Collection<int, OnboardingResponse> $responses */
        $responses = $session->responses;

        $responseMap = $responses
            ->mapWithKeys(
                static fn (OnboardingResponse $response): array => [
                    $response->step_key => is_array($response->response_payload)
                        ? $response->response_payload
                        : [],
                ]
            )
            ->all();

        return [
            'sessionToken' => (string) $session->access_token,
            'workspacePublicId' => (string) $session->public_id,
            'resumeUrl' => url('/start-project?session='.$session->access_token),
            'status' => (string) $session->status,
            'currentStep' => (int) $session->current_step,
            'lastSavedAt' => $session->last_saved_at?->toIso8601String(),
            'responses' => $responseMap,
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
        /** @var Prospect $prospect */
        $prospect = $session->prospect;

        if ($stepKey === 'project') {
            $projectDirection = Arr::get($payload, 'projectDirection');

            $prospect->forceFill([
                'project_direction' => $projectDirection,
            ])->save();

            if (is_string($projectDirection) && $projectDirection !== '') {
                LeadProjectInterest::query()->updateOrCreate(
                    [
                        'lead_id' => $prospect->id,
                        'project_type' => $projectDirection,
                    ],
                    ['summary' => Arr::get($payload, 'projectDirectionNotes')]
                );
            }

            return;
        }

        if ($stepKey !== 'business') {
            return;
        }

        $businessEmail = (string) Arr::get($payload, 'businessEmail', '');

        $prospect->forceFill([
            'primary_contact_name' => Arr::get($payload, 'contactName'),
            'primary_contact_role' => Arr::get($payload, 'roleInBusiness'),
            'business_name' => Arr::get($payload, 'businessName'),
            'primary_email' => $businessEmail !== '' ? $businessEmail : null,
            'normalized_primary_email' => $this->normalizeEmail($businessEmail),
            'primary_phone' => Arr::get($payload, 'phone'),
            'industry' => Arr::get($payload, 'industry'),
            'industry_other' => Arr::get($payload, 'industryOther'),
            'business_location' => Arr::get($payload, 'businessLocation'),
            'location_count' => Arr::get($payload, 'locationCount'),
            'employee_range' => Arr::get($payload, 'teamSize'),
        ])->save();

        $prospect->inquirySubmission?->update([
            'contact_name' => Arr::get($payload, 'contactName'),
            'business_name' => Arr::get($payload, 'businessName'),
            'email' => $businessEmail !== '' ? $businessEmail : null,
            'normalized_email' => $this->normalizeEmail($businessEmail),
            'phone' => Arr::get($payload, 'phone'),
            'status' => InquirySubmissionStatus::Reviewed->value,
            'submitted_at' => now(),
        ]);

        $prospect->contacts()->delete();

        $prospect->contacts()->create([
            'name' => Arr::get($payload, 'contactName'),
            'email' => $businessEmail !== '' ? $businessEmail : null,
            'normalized_email' => $this->normalizeEmail($businessEmail),
            'phone' => Arr::get($payload, 'phone'),
            'role' => Arr::get($payload, 'roleInBusiness'),
            'contact_method' => 'email',
            'is_primary' => true,
            'invite_later' => false,
        ]);

        if ($session->workspace !== null) {
            $session->workspace->members()->updateOrCreate(
                ['normalized_email' => $this->normalizeEmail($businessEmail)],
                [
                    'role' => ProspectWorkspaceMemberRole::Owner->value,
                    'access_scope' => ProspectWorkspaceAccessScope::Full->value,
                    'status' => ProspectWorkspaceMemberStatus::Active->value,
                    'name' => (string) Arr::get($payload, 'contactName', 'Workspace Owner'),
                    'email' => $businessEmail !== '' ? $businessEmail : 'pending@example.invalid',
                ]
            );
        }

        /** @var array<int, array<string, mixed>> $stakeholders */
        $stakeholders = Arr::wrap(Arr::get($payload, 'additionalStakeholders', []));

        foreach ($stakeholders as $stakeholder) {
            $name = trim((string) Arr::get($stakeholder, 'name', ''));
            $email = trim((string) Arr::get($stakeholder, 'email', ''));
            $role = trim((string) Arr::get($stakeholder, 'role', ''));

            if ($name === '' && $email === '' && $role === '') {
                continue;
            }

            $prospect->contacts()->create([
                'name' => $name !== '' ? $name : 'Additional Stakeholder',
                'email' => $email !== '' ? $email : null,
                'normalized_email' => $this->normalizeEmail($email),
                'role' => $role !== '' ? $role : null,
                'contact_method' => 'email',
                'is_primary' => false,
                'invite_later' => (bool) Arr::get($stakeholder, 'inviteLater', false),
            ]);

            if ($session->workspace !== null && $email !== '') {
                $session->workspace->members()->updateOrCreate(
                    ['normalized_email' => $this->normalizeEmail($email)],
                    [
                        'role' => ProspectWorkspaceMemberRole::Collaborator->value,
                        'access_scope' => ProspectWorkspaceAccessScope::Limited->value,
                        'status' => ProspectWorkspaceMemberStatus::Invited->value,
                        'name' => $name !== '' ? $name : 'Stakeholder',
                        'email' => $email,
                    ]
                );
            }
        }

        $prospect->forceFill([
            'status' => LeadStatus::WorkspaceActive->value,
            'converted_to_workspace_at' => now(),
        ])->save();
    }

    private function stepNumberForKey(string $stepKey): int
    {
        $position = array_search($stepKey, self::STEP_KEYS, true);

        if ($position === false) {
            return 1;
        }

        return $position + 1;
    }

    private function normalizeEmail(?string $email): ?string
    {
        if (! is_string($email)) {
            return null;
        }

        $normalized = strtolower(trim($email));

        return $normalized !== '' ? $normalized : null;
    }
}
