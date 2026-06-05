<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Pair
{
    public function __construct(public string $first, public string $second)
    {
    }
}
