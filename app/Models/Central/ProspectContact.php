<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProspectContact extends CentralModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'prospect_id',
        'name',
        'email',
        'phone',
        'role',
        'is_primary',
        'invite_later',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'invite_later' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Prospect, $this>
     */
    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }
}
