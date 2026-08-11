<?php

declare(strict_types=1);

namespace Tests\Feature\Onboarding;

use App\Models\Central\OnboardingAppointment;
use App\Models\Central\OnboardingResponse;
use App\Models\Central\OnboardingSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StartProjectOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_project_page_provisions_a_resumeable_onboarding_workspace(): void
    {
        $response = $this->get('/start-project');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Platform/StartProject')
                ->where('onboarding.currentStep', 1)
                ->where('onboarding.status', 'draft')
                ->where('onboarding.capabilities.canEdit', true)
                ->where('onboarding.capabilities.canContinueToScheduling', false)
                ->has('onboarding.sessionToken')
                ->has('options.stepKeys', 8)
            );

        $sessionToken = (string) $response->viewData('page')['props']['onboarding']['sessionToken'];

        $this->assertNotNull(
            OnboardingSession::query()->where('access_token', $sessionToken)->first()
        );
    }

    public function test_saving_step_persists_payload_and_revision_history(): void
    {
        $sessionToken = $this->startProjectSessionToken();

        $payload = [
            'businessName' => 'Harper Wellness Group',
            'contactName' => 'Alyssa Harper',
            'businessEmail' => 'alyssa@example.com',
            'phone' => '555-0199',
            'website' => 'https://harperwellness.example',
            'industry' => 'Healthcare',
            'businessType' => 'Wellness',
            'location' => 'Austin, TX',
            'contactRole' => 'Founder',
        ];

        $this->postJson('/start-project/session', [
            'sessionToken' => $sessionToken,
            'stepKey' => 'business',
            'payload' => $payload,
        ])->assertOk();

        $this->postJson('/start-project/session', [
            'sessionToken' => $sessionToken,
            'stepKey' => 'business',
            'payload' => array_merge($payload, ['phone' => '555-0200']),
        ])->assertOk();

        $session = OnboardingSession::query()
            ->where('access_token', $sessionToken)
            ->firstOrFail();

        $response = OnboardingResponse::query()
            ->where('onboarding_session_id', $session->id)
            ->where('step_key', 'business')
            ->firstOrFail();

        $this->assertSame('555-0200', $response->response_payload['phone']);
        $this->assertDatabaseCount('onboarding_response_revisions', 2);
    }

    public function test_complete_intake_redirects_into_zoom_onboarding_flow(): void
    {
        $sessionToken = $this->startProjectSessionToken();
        $this->saveRequiredIntakeSteps($sessionToken);

        $completeResponse = $this->postJson('/start-project/session/complete', [
            'sessionToken' => $sessionToken,
        ])->assertOk();

        $completeResponse->assertJsonPath(
            'redirectUrl',
            route('onboarding.start', ['discovery_session' => $sessionToken])
        );

        $session = OnboardingSession::query()
            ->where('access_token', $sessionToken)
            ->firstOrFail();

        $this->assertSame('intake_completed', $session->status);
    }

    public function test_onboarding_start_from_intake_creates_linked_zoom_appointment_and_shows_briefing(): void
    {
        $sessionToken = $this->startProjectSessionToken();
        $this->saveRequiredIntakeSteps($sessionToken);

        $this->postJson('/start-project/session/complete', [
            'sessionToken' => $sessionToken,
        ])->assertOk();

        $startResponse = $this->get(route('onboarding.start', ['discovery_session' => $sessionToken]));
        $startResponse->assertRedirect();

        $redirectLocation = (string) $startResponse->headers->get('Location', '');
        parse_str((string) parse_url($redirectLocation, PHP_URL_QUERY), $query);
        $token = (string) ($query['token'] ?? '');

        $this->assertNotSame('', $token);

        /** @var OnboardingAppointment $appointment */
        $appointment = OnboardingAppointment::query()->firstOrFail();
        $this->assertSame('Harper Wellness Group', $appointment->business_name);
        $this->assertSame('Alyssa Harper', $appointment->contact_name);
        $this->assertSame('alyssa@example.com', $appointment->contact_email);

        $this->get(route('onboarding.show', [
            'onboardingAppointment' => $appointment,
            'token' => $token,
        ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Onboarding/ScheduleMeeting')
                ->where('intakeSummary.business.businessName', 'Harper Wellness Group')
                ->where('intakeSummary.timeline.timelineExpectation', '1–3 months')
            );
    }

    private function startProjectSessionToken(): string
    {
        $response = $this->get('/start-project');

        return (string) $response->viewData('page')['props']['onboarding']['sessionToken'];
    }

    private function saveRequiredIntakeSteps(string $sessionToken): void
    {
        $this->postJson('/start-project/session', [
            'sessionToken' => $sessionToken,
            'stepKey' => 'business',
            'payload' => [
                'businessName' => 'Harper Wellness Group',
                'contactName' => 'Alyssa Harper',
                'businessEmail' => 'alyssa@example.com',
                'phone' => '555-0199',
                'website' => 'https://harperwellness.example',
                'industry' => 'Healthcare',
                'businessType' => 'Wellness',
                'location' => 'Austin, TX',
                'contactRole' => 'Founder',
            ],
        ])->assertOk();

        $this->postJson('/start-project/session', [
            'sessionToken' => $sessionToken,
            'stepKey' => 'project',
            'payload' => [
                'projectTypes' => ['customer-portal'],
                'projectTypeOther' => null,
                'projectSummary' => 'Customer portal and website redesign.',
            ],
        ])->assertOk();

        $this->postJson('/start-project/session', [
            'sessionToken' => $sessionToken,
            'stepKey' => 'overview',
            'payload' => [
                'whatToBuild' => 'A portal where customers can track service requests.',
                'problemToSolve' => 'Phone and email updates are inconsistent.',
                'businessGoals' => 'Faster updates and better retention.',
                'primaryUsers' => 'Existing and new service customers.',
            ],
        ])->assertOk();

        $this->postJson('/start-project/session', [
            'sessionToken' => $sessionToken,
            'stepKey' => 'features',
            'payload' => [
                'requestedFeatures' => ['User Accounts / Login', 'Messaging'],
                'otherFeatures' => null,
            ],
        ])->assertOk();

        $this->postJson('/start-project/session', [
            'sessionToken' => $sessionToken,
            'stepKey' => 'assets',
            'payload' => [
                'existingAssets' => ['Existing website', 'Owned domain'],
                'currentWebsiteUrl' => 'https://harperwellness.example',
                'domainName' => 'harperwellness.example',
                'hostingProvider' => null,
                'existingPlatform' => 'WordPress',
                'existingSoftware' => null,
                'knownIntegrations' => 'HubSpot',
                'dataNotes' => null,
            ],
        ])->assertOk();

        $this->postJson('/start-project/session', [
            'sessionToken' => $sessionToken,
            'stepKey' => 'branding',
            'payload' => [
                'hasLogo' => true,
                'hasBrandColors' => true,
                'hasBrandGuidelines' => false,
                'inspirationLinks' => ['https://example.com/reference'],
                'inspirationNotes' => 'Clean and modern feel.',
                'designDirection' => 'Start fresh but keep brand colors.',
            ],
        ])->assertOk();

        $this->postJson('/start-project/session', [
            'sessionToken' => $sessionToken,
            'stepKey' => 'timeline',
            'payload' => [
                'targetLaunchDate' => null,
                'timelineExpectation' => '1–3 months',
                'urgency' => 'High',
                'deadlineContext' => 'Preparing for seasonal campaign.',
                'budgetExpectation' => '$25k - $50k',
            ],
        ])->assertOk();
    }
}
