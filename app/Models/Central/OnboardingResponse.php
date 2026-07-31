<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
