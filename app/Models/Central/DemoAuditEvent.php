<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Shared\Database\CentralModel;

class DemoAuditEvent extends CentralModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_type',
        'tenant_scope',
        'payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }
}
