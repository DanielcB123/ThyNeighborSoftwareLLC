<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Prospect extends CentralModel
{
    use HasPublicId;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'intake_status',
        'project_direction',
        'business_name',
        'business_email',
        'business_phone',
        'industry',
        'industry_other',
        'business_location',
        'location_count',
        'team_size',
        'primary_contact_name',
        'primary_contact_role',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'location_count' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<ProspectContact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(ProspectContact::class);
    }

    /**
     * @return HasMany<OnboardingSession, $this>
     */
    public function onboardingSessions(): HasMany
    {
        return $this->hasMany(OnboardingSession::class);
    }

    /**
     * @return HasOne<DiscoveryMeeting, $this>
     */
    public function discoveryMeeting(): HasOne
    {
        return $this->hasOne(DiscoveryMeeting::class);
    }

    /**
     * @return HasMany<ProjectRequirement, $this>
     */
    public function requirements(): HasMany
    {
        return $this->hasMany(ProjectRequirement::class);
    }

    /**
     * @return HasMany<ProposedDeliverable, $this>
     */
    public function proposedDeliverables(): HasMany
    {
        return $this->hasMany(ProposedDeliverable::class);
    }

    /**
     * @return HasMany<ClientReview, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ClientReview::class);
    }
}
