<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadProjectInterest extends CentralModel
{
    use HasPublicId;

    protected $table = 'lead_project_interests';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'lead_id',
        'project_type',
        'summary',
    ];

    /**
     * @return BelongsTo<Prospect, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Prospect::class, 'lead_id');
    }
}
