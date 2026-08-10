<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingResponse extends CentralModel
{
    use HasPublicId;

    protected $table = 'discovery_responses';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'discovery_session_id',
        'question_key',
        'response_payload',
        'current_response_key',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'response_payload' => 'array',
            'current_response_key' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function getStepKeyAttribute(): string
    {
        return (string) $this->question_key;
    }

    /**
     * @return BelongsTo<OnboardingSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(OnboardingSession::class, 'discovery_session_id');
    }
}
