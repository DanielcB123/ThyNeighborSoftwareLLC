<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProspectWorkspaceMember extends CentralModel
{
    use HasPublicId;

    protected $table = 'prospect_workspace_members';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'prospect_workspace_id',
        'invited_by_user_id',
        'role',
        'access_scope',
        'status',
        'name',
        'email',
        'normalized_email',
        'last_seen_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ProspectWorkspace, $this>
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(ProspectWorkspace::class, 'prospect_workspace_id');
    }
}
