<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProspectWorkspace extends CentralModel
{
    use HasPublicId;

    protected $table = 'prospect_workspaces';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'lead_id',
        'status',
        'access_scope',
        'active_workspace_key',
        'workspace_name',
        'opened_at',
        'discovery_completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active_workspace_key' => 'integer',
            'opened_at' => 'datetime',
            'discovery_completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Prospect, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Prospect::class, 'lead_id');
    }

    /**
     * @return HasMany<ProspectWorkspaceMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(ProspectWorkspaceMember::class, 'prospect_workspace_id');
    }

    /**
     * @return HasMany<OnboardingSession, $this>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(OnboardingSession::class, 'prospect_workspace_id');
    }
}
