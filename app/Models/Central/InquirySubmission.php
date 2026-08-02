<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InquirySubmission extends CentralModel
{
    use HasPublicId;

    protected $table = 'inquiry_submissions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'lead_source_id',
        'status',
        'spam_disposition',
        'contact_name',
        'business_name',
        'email',
        'normalized_email',
        'phone',
        'website',
        'message',
        'context',
        'submitted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'context' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<LeadSource, $this>
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    /**
     * @return HasOne<Prospect, $this>
     */
    public function lead(): HasOne
    {
        return $this->hasOne(Prospect::class, 'inquiry_submission_id');
    }
}
