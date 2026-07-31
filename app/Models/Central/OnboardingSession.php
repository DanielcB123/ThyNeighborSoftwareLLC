<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OnboardingSession extends CentralModel
{
    use HasPublicId;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'prospect_id',
        'access_token',
        'status',
        'current_step',
        'last_saved_at',
        'completed_at',
        'last_activity_ip',
        'last_activity_user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'current_step' => 'integer',
            'last_saved_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Prospect, $this>
     */
    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    /**
     * @return HasMany<OnboardingResponse, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(OnboardingResponse::class);
    }

    /**
     * @return HasOne<DiscoveryMeeting, $this>
     */
    public function discoveryMeeting(): HasOne
    {
        return $this->hasOne(DiscoveryMeeting::class);
    }
}
