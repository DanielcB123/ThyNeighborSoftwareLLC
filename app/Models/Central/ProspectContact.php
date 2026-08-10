<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProspectContact extends CentralModel
{
    use HasPublicId;

    protected $table = 'lead_contacts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'lead_id',
        'name',
        'email',
        'normalized_email',
        'phone',
        'role',
        'contact_method',
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
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    /**
     * @deprecated Prefer {@see self::lead()}.
     *
     * @return BelongsTo<Lead, $this>
     */
    public function prospect(): BelongsTo
    {
        return $this->lead();
    }
}
