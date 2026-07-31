<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscoveryNote extends CentralModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'discovery_meeting_id',
        'author_name',
        'note_text',
        'note_context',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'note_context' => 'array',
        ];
    }

    /**
     * @return BelongsTo<DiscoveryMeeting, $this>
     */
    public function meeting(): BelongsTo
    {
        return $this->belongsTo(DiscoveryMeeting::class, 'discovery_meeting_id');
    }
}
