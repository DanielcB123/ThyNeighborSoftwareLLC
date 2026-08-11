<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnboardingResponse extends CentralModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'onboarding_session_id',
        'step_key',
        'response_payload',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'response_payload' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<OnboardingSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(OnboardingSession::class, 'onboarding_session_id');
    }

    /**
     * @return HasMany<OnboardingResponseRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(OnboardingResponseRevision::class, 'onboarding_response_id');
    }
}
