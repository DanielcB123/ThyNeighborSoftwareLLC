<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingResponseRevision extends CentralModel
{
    use HasPublicId;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'onboarding_response_id',
        'revision_number',
        'response_payload',
        'changed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'revision_number' => 'integer',
            'response_payload' => 'array',
            'changed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<OnboardingResponse, $this>
     */
    public function response(): BelongsTo
    {
        return $this->belongsTo(OnboardingResponse::class, 'onboarding_response_id');
    }
}
