<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRequirement extends CentralModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'prospect_id',
        'discovery_meeting_id',
        'title',
        'description',
        'priority',
        'status',
    ];

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
