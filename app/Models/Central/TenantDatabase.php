<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantDatabase extends CentralModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'database_cluster_id',
        'database_name',
        'status',
        'secret_reference',
        'schema_version',
        'provisioning_state',
        'migration_state',
        'health_state',
        'is_current',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return BelongsTo<DatabaseCluster, $this>
     */
    public function cluster(): BelongsTo
    {
        return $this->belongsTo(DatabaseCluster::class, 'database_cluster_id');
    }
}
