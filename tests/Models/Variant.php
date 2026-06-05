<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Variant
{
    public function __construct(public string|int $value)
    {
    }
}
