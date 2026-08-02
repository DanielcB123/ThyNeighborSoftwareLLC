<?php

declare(strict_types=1);

namespace Tests\Feature\Onboarding;

use App\Models\Central\DiscoveryMeeting;
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
                ->where('onboarding.status', 'in_progress')
                ->has('onboarding.sessionToken')
                ->has('options.stepKeys', 5)
            );

        $sessionToken = $response->viewData('page')['props']['onboarding']['sessionToken'];

        $this->assertNotNull(
            OnboardingSession::query()->where('access_token', $sessionToken)->first()
        );
    }

    public function test_business_step_saves_prospect_context_and_stakeholders(): void
    {
        $startResponse = $this->get('/start-project');
        $sessionToken = $startResponse->viewData('page')['props']['onboarding']['sessionToken'];

        $this->postJson('/start-project/session', [
            'sessionToken' => $sessionToken,
            'stepKey' => 'business',
            'payload' => [
                'contactName' => 'Alyssa Harper',
                'businessName' => 'Harper Wellness Group',
                'businessEmail' => 'alyssa@example.com',
                'phone' => '555-0199',
                'industry' => 'Healthcare',
                'industryOther' => null,
                'businessLocation' => 'Austin, TX',
                'locationCount' => 3,
                'teamSize' => '26-50',
                'roleInBusiness' => 'Founder',
                'additionalStakeholders' => [
                    [
                        'name' => 'Jordan Operations',
                        'email' => 'jordan@example.com',
                        'role' => 'Operations',
                        'inviteLater' => true,
                    ],
                ],
            ],
        ])->assertOk();

        $session = OnboardingSession::query()
            ->where('access_token', $sessionToken)
            ->firstOrFail();

        $this->assertDatabaseHas('leads', [
            'id' => $session->lead_id,
            'business_name' => 'Harper Wellness Group',
            'primary_email' => 'alyssa@example.com',
            'industry' => 'Healthcare',
            'location_count' => 3,
            'primary_contact_name' => 'Alyssa Harper',
        ]);

        $this->assertDatabaseHas('lead_contacts', [
            'lead_id' => $session->lead_id,
            'name' => 'Alyssa Harper',
            'is_primary' => true,
        ]);

        $this->assertDatabaseHas('lead_contacts', [
            'lead_id' => $session->lead_id,
            'name' => 'Jordan Operations',
            'invite_later' => true,
        ]);
    }

    public function test_discovery_meeting_request_finalizes_the_intake_session(): void
    {
        $startResponse = $this->get('/start-project');
        $sessionToken = $startResponse->viewData('page')['props']['onboarding']['sessionToken'];

        $response = $this->postJson('/start-project/session/schedule', [
            'sessionToken' => $sessionToken,
            'payload' => [
                'meetingFormat' => 'video',
                'timezone' => 'America/Chicago',
                'preferredStartDate' => now()->addDays(2)->toDateString(),
                'preferredEndDate' => now()->addDays(5)->toDateString(),
                'availabilityNotes' => 'Weekday mornings work best for leadership stakeholders.',
                'attendees' => [
                    [
                        'name' => 'Alyssa Harper',
                        'email' => 'alyssa@example.com',
                        'role' => 'Founder',
                    ],
                ],
            ],
        ])->assertOk();

        $meetingPublicId = $response->json('meetingPublicId');
        $meeting = DiscoveryMeeting::query()->where('public_id', $meetingPublicId)->firstOrFail();

        $this->assertSame('requested', $meeting->status);
        $this->assertSame('America/Chicago', $meeting->timezone);

        $session = OnboardingSession::query()->where('access_token', $sessionToken)->firstOrFail();
        $this->assertSame('discovery_complete', $session->status);
        $this->assertNotNull($session->completed_at);
    }

    public function test_contact_page_now_routes_primary_action_to_start_project(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Platform/Home')
                ->where('blocks.0.data.primaryActionPath', '/start-project')
            );
    }
}
