<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatabaseCluster extends CentralModel
{
    use HasPublicId;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'name',
        'host',
        'port',
        'ssl_mode',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<TenantDatabase, $this>
     */
    public function tenantDatabases(): HasMany
    {
        return $this->hasMany(TenantDatabase::class, 'database_cluster_id');
    }
}
