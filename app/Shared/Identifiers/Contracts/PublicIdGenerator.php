<?php

namespace App\Shared\Identifiers\Contracts;

interface PublicIdGenerator
{
    public function generate(): string;
}
