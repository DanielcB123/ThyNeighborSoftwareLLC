<?php

namespace Tests\Unit\Shared;

use App\Shared\Identifiers\UlidPublicIdGenerator;
use PHPUnit\Framework\TestCase;

class UlidPublicIdGeneratorTest extends TestCase
{
    public function test_it_generates_26_character_base32_ulids(): void
    {
        $generator = new UlidPublicIdGenerator();

        $publicId = $generator->generate();

        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $publicId);
    }

    public function test_it_generates_unique_values(): void
    {
        $generator = new UlidPublicIdGenerator();

        $this->assertNotSame($generator->generate(), $generator->generate());
    }
}
