<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingSubmission extends CentralModel
{
    use HasPublicId;

    protected $table = 'discovery_submission_versions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'discovery_session_id',
        'submitted_by_member_id',
        'version_number',
        'submitted_payload',
        'submitted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'submitted_payload' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<OnboardingSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(OnboardingSession::class, 'discovery_session_id');
    }
}
