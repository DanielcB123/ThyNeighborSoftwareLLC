<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadSource extends CentralModel
{
    use HasPublicId;

    protected $table = 'lead_sources';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'name',
        'slug',
        'status',
        'description',
    ];

    /**
     * @return HasMany<InquirySubmission, $this>
     */
    public function inquirySubmissions(): HasMany
    {
        return $this->hasMany(InquirySubmission::class, 'lead_source_id');
    }
}
