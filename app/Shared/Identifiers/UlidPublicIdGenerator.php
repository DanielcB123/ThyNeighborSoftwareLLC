<?php

namespace App\Shared\Identifiers;

use App\Shared\Identifiers\Contracts\PublicIdGenerator;
use Illuminate\Support\Str;

final class UlidPublicIdGenerator implements PublicIdGenerator
{
    public function generate(): string
    {
        return Str::ulid()->toBase32();
    }
}
