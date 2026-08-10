<?php

declare(strict_types=1);

namespace App\Onboarding\Services;

use App\Enums\Onboarding\DiscoverySessionStatus;
use App\Models\Central\OnboardingSession;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DiscoverySessionWorkflow
{
    public const ACTOR_CLIENT = 'client';
    public const ACTOR_INTERNAL = 'internal';
    public const ACTOR_SYSTEM = 'system';

    /**
     * @var list<string>
     */
    private const REQUIRED_MEETING_STEP_KEYS = ['project', 'business', 'goals', 'meeting'];

    /**
     * @var array<string, list<string>>
     */
    private const STEP_DEPENDENCIES = [
        'project' => [],
        'business' => ['project'],
        'goals' => ['project', 'business'],
        'preparation' => ['project', 'business', 'goals'],
        'meeting' => ['project', 'business', 'goals'],
    ];

    /**
     * @var array<string, list<array{
     *   to: string,
     *   actors: list<string>,
     *   required_steps: list<string>,
     *   requires_meeting_request: bool,
     *   locks_client_editing: bool,
     *   creates_response_revision: bool,
     *   records_audit_event: bool,
     *   dispatches_notification: bool
     * }>>
     */
    private const TRANSITIONS = [
        'draft' => [
            [
                'to' => 'project_discovery_in_progress',
                'actors' => [self::ACTOR_CLIENT, self::ACTOR_SYSTEM],
                'required_steps' => ['project'],
                'requires_meeting_request' => false,
                'locks_client_editing' => false,
                'creates_response_revision' => false,
                'records_audit_event' => true,
                'dispatches_notification' => false,
            ],
            [
                'to' => 'withdrawn',
                'actors' => [self::ACTOR_CLIENT, self::ACTOR_INTERNAL],
                'required_steps' => [],
                'requires_meeting_request' => false,
                'locks_client_editing' => true,
                'creates_response_revision' => false,
                'records_audit_event' => true,
                'dispatches_notification' => false,
            ],
        ],
        'project_discovery_in_progress' => [
            [
                'to' => 'meeting_requested',
                'actors' => [self::ACTOR_CLIENT],
                'required_steps' => self::REQUIRED_MEETING_STEP_KEYS,
                'requires_meeting_request' => true,
                'locks_client_editing' => true,
                'creates_response_revision' => false,
                'records_audit_event' => true,
                'dispatches_notification' => true,
            ],
            [
                'to' => 'withdrawn',
                'actors' => [self::ACTOR_CLIENT, self::ACTOR_INTERNAL],
                'required_steps' => [],
                'requires_meeting_request' => false,
                'locks_client_editing' => true,
                'creates_response_revision' => false,
                'records_audit_event' => true,
                'dispatches_notification' => false,
            ],
        ],
        'client_revision_in_progress' => [
            [
                'to' => 'meeting_requested',
                'actors' => [self::ACTOR_CLIENT],
                'required_steps' => self::REQUIRED_MEETING_STEP_KEYS,
                'requires_meeting_request' => true,
                'locks_client_editing' => true,
                'creates_response_revision' => true,
                'records_audit_event' => true,
                'dispatches_notification' => true,
            ],
        ],
        'meeting_requested' => [
            [
                'to' => 'submitted',
                'actors' => [self::ACTOR_SYSTEM],
                'required_steps' => self::REQUIRED_MEETING_STEP_KEYS,
                'requires_meeting_request' => true,
                'locks_client_editing' => true,
                'creates_response_revision' => false,
                'records_audit_event' => true,
                'dispatches_notification' => true,
            ],
            [
                'to' => 'withdrawn',
                'actors' => [self::ACTOR_CLIENT, self::ACTOR_INTERNAL],
                'required_steps' => [],
                'requires_meeting_request' => false,
                'locks_client_editing' => true,
                'creates_response_revision' => false,
                'records_audit_event' => true,
                'dispatches_notification' => false,
            ],
        ],
        'submitted' => [
            [
                'to' => 'under_internal_review',
                'actors' => [self::ACTOR_INTERNAL, self::ACTOR_SYSTEM],
                'required_steps' => self::REQUIRED_MEETING_STEP_KEYS,
                'requires_meeting_request' => true,
                'locks_client_editing' => true,
                'creates_response_revision' => false,
                'records_audit_event' => true,
                'dispatches_notification' => false,
            ],
            [
                'to' => 'withdrawn',
                'actors' => [self::ACTOR_INTERNAL],
                'required_steps' => [],
                'requires_meeting_request' => false,
                'locks_client_editing' => true,
                'creates_response_revision' => false,
                'records_audit_event' => true,
                'dispatches_notification' => false,
            ],
        ],
        'under_internal_review' => [
            [
                'to' => 'clarification_requested',
                'actors' => [self::ACTOR_INTERNAL],
                'required_steps' => self::REQUIRED_MEETING_STEP_KEYS,
                'requires_meeting_request' => true,
                'locks_client_editing' => true,
                'creates_response_revision' => false,
                'records_audit_event' => true,
                'dispatches_notification' => true,
            ],
            [
                'to' => 'approved_for_handoff',
                'actors' => [self::ACTOR_INTERNAL],
                'required_steps' => self::REQUIRED_MEETING_STEP_KEYS,
                'requires_meeting_request' => true,
                'locks_client_editing' => true,
                'creates_response_revision' => false,
                'records_audit_event' => true,
                'dispatches_notification' => true,
            ],
            [
                'to' => 'rejected',
                'actors' => [self::ACTOR_INTERNAL],
                'required_steps' => self::REQUIRED_MEETING_STEP_KEYS,
                'requires_meeting_request' => true,
                'locks_client_editing' => true,
                'creates_response_revision' => false,
                'records_audit_event' => true,
                'dispatches_notification' => true,
            ],
        ],
        'clarification_requested' => [
            [
                'to' => 'client_revision_in_progress',
                'actors' => [self::ACTOR_CLIENT, self::ACTOR_SYSTEM],
                'required_steps' => ['project', 'business'],
                'requires_meeting_request' => false,
                'locks_client_editing' => false,
                'creates_response_revision' => true,
                'records_audit_event' => true,
                'dispatches_notification' => false,
            ],
        ],
        'approved_for_handoff' => [],
        'rejected' => [],
        'withdrawn' => [],
        'archived' => [],
    ];

    public function capabilities(OnboardingSession $session): array
    {
        $status = $this->resolveStatus($session);
        $completedSteps = $this->completedStepKeys($session);
        $canEdit = $this->isClientEditableStatus($status);

        return [
            'canEdit' => $canEdit,
            'canRequestMeeting' => $canEdit && $this->hasRequiredSteps(
                self::REQUIRED_MEETING_STEP_KEYS,
                $completedSteps
            ) && in_array(
                $status->value,
                [
                    DiscoverySessionStatus::ProjectDiscoveryInProgress->value,
                    DiscoverySessionStatus::ClientRevisionInProgress->value,
                ],
                true
            ),
            'requiredMeetingSteps' => self::REQUIRED_MEETING_STEP_KEYS,
            'completedSteps' => array_values($completedSteps),
            'availableStatusTransitions' => $this->availableTransitionTargets(
                $session,
                self::ACTOR_CLIENT
            ),
        ];
    }

    public function markProgressStarted(OnboardingSession $session): void
    {
        $status = $this->resolveStatus($session);

        if ($status !== DiscoverySessionStatus::Draft) {
            return;
        }

        $this->transition(
            $session,
            DiscoverySessionStatus::ProjectDiscoveryInProgress,
            self::ACTOR_SYSTEM
        );
    }

    /**
     * @throws ValidationException
     */
    public function ensureStepCanBeSaved(OnboardingSession $session, string $stepKey): void
    {
        $status = $this->resolveStatus($session);

        if (! $this->isClientEditableStatus($status)) {
            throw ValidationException::withMessages([
                'sessionToken' => ['This onboarding session is locked and can no longer be edited.'],
            ]);
        }

        $completedSteps = $this->completedStepKeys($session);
        $requiredDependencies = Arr::get(self::STEP_DEPENDENCIES, $stepKey, []);

        if (! $this->hasRequiredSteps($requiredDependencies, $completedSteps)) {
            throw ValidationException::withMessages([
                'stepKey' => ['Complete the earlier onboarding steps before saving this step.'],
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    public function transition(
        OnboardingSession $session,
        DiscoverySessionStatus $targetStatus,
        string $actor
    ): OnboardingSession {
        $currentStatus = $this->resolveStatus($session);

        if ($currentStatus === $targetStatus) {
            return $session;
        }

        $transition = $this->matchingTransition($session, $currentStatus, $targetStatus, $actor);
        $session->forceFill([
            'status' => $targetStatus->value,
            'last_saved_at' => now(),
            'completed_at' => $transition['locks_client_editing'] ? ($session->completed_at ?? now()) : null,
        ])->save();

        if ($transition['records_audit_event']) {
            $this->recordAuditEvent($session, $currentStatus, $targetStatus, $actor);
        }

        if ($transition['dispatches_notification']) {
            event('onboarding.discovery-session.transitioned', [
                'session_id' => $session->id,
                'from_status' => $currentStatus->value,
                'to_status' => $targetStatus->value,
                'actor' => $actor,
            ]);
        }

        return $session->refresh();
    }

    /**
     * @return list<string>
     */
    private function availableTransitionTargets(OnboardingSession $session, string $actor): array
    {
        $status = $this->resolveStatus($session);
        $completedSteps = $this->completedStepKeys($session);
        $hasMeetingRequest = $session->discoveryMeeting()->exists();

        return collect(self::TRANSITIONS[$status->value] ?? [])
            ->filter(function (array $rule) use ($actor, $completedSteps, $hasMeetingRequest): bool {
                if (! in_array($actor, $rule['actors'], true)) {
                    return false;
                }

                if (
                    $rule['requires_meeting_request'] === true &&
                    ! $hasMeetingRequest
                ) {
                    return false;
                }

                return $this->hasRequiredSteps($rule['required_steps'], $completedSteps);
            })
            ->pluck('to')
            ->values()
            ->all();
    }

    /**
     * @return array{
     *   to: string,
     *   actors: list<string>,
     *   required_steps: list<string>,
     *   requires_meeting_request: bool,
     *   locks_client_editing: bool,
     *   creates_response_revision: bool,
     *   records_audit_event: bool,
     *   dispatches_notification: bool
     * }
     */
    private function matchingTransition(
        OnboardingSession $session,
        DiscoverySessionStatus $currentStatus,
        DiscoverySessionStatus $targetStatus,
        string $actor
    ): array {
        $completedSteps = $this->completedStepKeys($session);
        $hasMeetingRequest = $session->discoveryMeeting()->exists();

        foreach (self::TRANSITIONS[$currentStatus->value] ?? [] as $rule) {
            if ($rule['to'] !== $targetStatus->value) {
                continue;
            }

            if (! in_array($actor, $rule['actors'], true)) {
                break;
            }

            if (
                $rule['requires_meeting_request'] === true &&
                ! $hasMeetingRequest
            ) {
                break;
            }

            if (! $this->hasRequiredSteps($rule['required_steps'], $completedSteps)) {
                break;
            }

            return $rule;
        }

        throw ValidationException::withMessages([
            'status' => [sprintf(
                'Cannot transition onboarding session from %s to %s.',
                $currentStatus->value,
                $targetStatus->value
            )],
        ]);
    }

    /**
     * @return list<string>
     */
    private function completedStepKeys(OnboardingSession $session): array
    {
        $session->loadMissing('responses');

        return $session->responses
            ->filter(static function ($response): bool {
                return is_array($response->response_payload)
                    && $response->response_payload !== [];
            })
            ->pluck('question_key')
            ->map(static fn (mixed $key): string => (string) $key)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $requiredSteps
     * @param  list<string>  $completedSteps
     */
    private function hasRequiredSteps(array $requiredSteps, array $completedSteps): bool
    {
        if ($requiredSteps === []) {
            return true;
        }

        return collect($requiredSteps)->every(
            static fn (string $requiredStep): bool => in_array($requiredStep, $completedSteps, true)
        );
    }

    private function isClientEditableStatus(DiscoverySessionStatus $status): bool
    {
        return in_array(
            $status->value,
            [
                DiscoverySessionStatus::Draft->value,
                DiscoverySessionStatus::ProjectDiscoveryInProgress->value,
                DiscoverySessionStatus::ClarificationRequested->value,
                DiscoverySessionStatus::ClientRevisionInProgress->value,
            ],
            true
        );
    }

    private function resolveStatus(OnboardingSession $session): DiscoverySessionStatus
    {
        return DiscoverySessionStatus::tryFrom((string) $session->status)
            ?? DiscoverySessionStatus::Draft;
    }

    private function recordAuditEvent(
        OnboardingSession $session,
        DiscoverySessionStatus $from,
        DiscoverySessionStatus $to,
        string $actor
    ): void {
        DB::table('lead_activities')->insert([
            'public_id' => (string) \Illuminate\Support\Str::ulid(),
            'lead_id' => $session->lead_id,
            'activity_type' => 'onboarding.session.transition',
            'visibility' => 'internal',
            'summary' => sprintf(
                'Discovery session transitioned from %s to %s by %s.',
                $from->value,
                $to->value,
                $actor
            ),
            'details' => null,
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
