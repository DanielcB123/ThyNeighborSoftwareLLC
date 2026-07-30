<?php

namespace App\Shared\Database\Concerns;

use App\Shared\Identifiers\Contracts\PublicIdGenerator;
use App\Shared\Identifiers\UlidPublicIdGenerator;

trait HasPublicId
{
    public static function bootHasPublicId(): void
    {
        static::creating(function ($model): void {
            $model->setAttribute('public_id', static::resolvePublicIdGenerator()->generate());
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    private static function resolvePublicIdGenerator(): PublicIdGenerator
    {
        if (app()->bound(PublicIdGenerator::class)) {
            return app(PublicIdGenerator::class);
        }

        return new UlidPublicIdGenerator();
    }
}
