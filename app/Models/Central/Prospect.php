<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Prospect extends CentralModel
{
    use HasPublicId;

    protected $table = 'leads';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'inquiry_submission_id',
        'status',
        'priority',
        'business_stage',
        'project_direction',
        'business_name',
        'primary_email',
        'primary_phone',
        'industry',
        'industry_other',
        'business_location',
        'location_count',
        'employee_range',
        'primary_contact_name',
        'primary_contact_role',
        'converted_to_workspace_at',
        'discovery_completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'location_count' => 'integer',
            'converted_to_workspace_at' => 'datetime',
            'discovery_completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<InquirySubmission, $this>
     */
    public function inquirySubmission(): BelongsTo
    {
        return $this->belongsTo(InquirySubmission::class);
    }

    /**
     * @return HasMany<ProspectContact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(ProspectContact::class, 'lead_id');
    }

    /**
     * @return HasMany<OnboardingSession, $this>
     */
    public function onboardingSessions(): HasMany
    {
        return $this->hasMany(OnboardingSession::class, 'lead_id');
    }

    /**
     * @return HasOne<ProspectWorkspace, $this>
     */
    public function activeWorkspace(): HasOne
    {
        return $this->hasOne(ProspectWorkspace::class, 'lead_id')
            ->where('active_workspace_key', 1);
    }

    /**
     * @return HasOne<DiscoveryMeeting, $this>
     */
    public function discoveryMeeting(): HasOne
    {
        return $this->hasOne(DiscoveryMeeting::class, 'lead_id');
    }

}
