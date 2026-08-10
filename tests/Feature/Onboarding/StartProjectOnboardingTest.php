<?php

declare(strict_types=1);

namespace Tests\Feature\Onboarding;

use App\Models\Central\DiscoveryMeeting;
use App\Models\Central\OnboardingSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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
            'stepKey' => 'project',
            'payload' => [
                'projectDirection' => 'new-website',
                'projectDirectionNotes' => null,
            ],
        ])->assertOk();

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

        $this->postJson('/start-project/session', [
            'sessionToken' => $sessionToken,
            'stepKey' => 'project',
            'payload' => [
                'projectDirection' => 'custom-web-application',
                'projectDirectionNotes' => 'Client portal and automation workflows.',
            ],
        ])->assertOk();

        $this->postJson('/start-project/session', [
            'sessionToken' => $sessionToken,
            'stepKey' => 'business',
            'payload' => [
                'contactName' => 'Alyssa Harper',
                'businessName' => 'Harper Wellness Group',
                'businessEmail' => 'alyssa-finalize@example.com',
                'phone' => '555-0199',
                'industry' => 'Healthcare',
                'industryOther' => null,
                'businessLocation' => 'Austin, TX',
                'locationCount' => 3,
                'teamSize' => '26-50',
                'roleInBusiness' => 'Founder',
                'additionalStakeholders' => [],
            ],
        ])->assertOk();

        $this->postJson('/start-project/session', [
            'sessionToken' => $sessionToken,
            'stepKey' => 'goals',
            'payload' => [
                'goals' => ['Bring in more customers'],
                'goalsOther' => null,
                'currentProblems' => 'Manual intake is inconsistent and slow.',
                'successLooksLike' => 'A reliable intake and booking workflow.',
                'knownConstraints' => null,
                'timelineComfort' => 'Within 3 to 6 months',
                'budgetComfort' => 'Ready for a meaningful investment',
            ],
        ])->assertOk();

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
        $this->assertSame('submitted', $session->status);
        $this->assertNotNull($session->completed_at);

        $this->postJson('/start-project/session', [
            'sessionToken' => $sessionToken,
            'stepKey' => 'project',
            'payload' => [
                'projectDirection' => 'website-redesign',
                'projectDirectionNotes' => null,
            ],
        ])->assertStatus(422);
    }

    public function test_meeting_request_requires_prior_required_steps(): void
    {
        $startResponse = $this->get('/start-project');
        $sessionToken = $startResponse->viewData('page')['props']['onboarding']['sessionToken'];

        $this->postJson('/start-project/session/schedule', [
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
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['stepKey']);
    }

    public function test_duplicate_business_email_is_blocked_for_active_intake(): void
    {
        $firstStartResponse = $this->get('/start-project');
        $firstSessionToken = $firstStartResponse->viewData('page')['props']['onboarding']['sessionToken'];

        $secondStartResponse = $this
            ->withServerVariables([
                'REMOTE_ADDR' => '203.0.113.10',
                'HTTP_USER_AGENT' => 'OnboardingDuplicateTest/1.0',
            ])
            ->withCookie('start_project_session', Str::random(64))
            ->get('/start-project');
        $secondSessionToken = $secondStartResponse->viewData('page')['props']['onboarding']['sessionToken'];

        $businessPayload = [
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
            'additionalStakeholders' => [],
        ];

        $this->postJson('/start-project/session', [
            'sessionToken' => $firstSessionToken,
            'stepKey' => 'project',
            'payload' => [
                'projectDirection' => 'new-website',
                'projectDirectionNotes' => null,
            ],
        ])->assertOk();

        $this->postJson('/start-project/session', [
            'sessionToken' => $firstSessionToken,
            'stepKey' => 'business',
            'payload' => $businessPayload,
        ])->assertOk();

        $this->postJson('/start-project/session', [
            'sessionToken' => $secondSessionToken,
            'stepKey' => 'project',
            'payload' => [
                'projectDirection' => 'website-redesign',
                'projectDirectionNotes' => null,
            ],
        ])->assertOk();

        $this->postJson('/start-project/session', [
            'sessionToken' => $secondSessionToken,
            'stepKey' => 'business',
            'payload' => $businessPayload,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['payload.businessEmail']);
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
