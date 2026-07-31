<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientReview extends CentralModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'prospect_id',
        'discovery_meeting_id',
        'status',
        'summary',
        'reviewed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
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
     * @return BelongsTo<DiscoveryMeeting, $this>
     */
    public function meeting(): BelongsTo
    {
        return $this->belongsTo(DiscoveryMeeting::class, 'discovery_meeting_id');
    }
}
