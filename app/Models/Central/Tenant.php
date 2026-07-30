<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;
use App\Shared\Database\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends CentralModel
{
    use HasPublicId;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'slug',
        'display_name',
        'status',
        'locale',
        'timezone',
        'enabled_modules',
        'enabled_capabilities',
        'provisioning_state',
        'migration_state',
        'health_state',
        'is_demo',
        'demo_dataset_version',
        'demo_seeded_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enabled_modules' => 'array',
            'enabled_capabilities' => 'array',
            'is_demo' => 'boolean',
            'demo_seeded_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<TenantDomain, $this>
     */
    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class);
    }

    /**
     * @return HasMany<TenantDatabase, $this>
     */
    public function tenantDatabases(): HasMany
    {
        return $this->hasMany(TenantDatabase::class);
    }
}
