<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Uuid
{
    public function __construct(public string $value)
    {
    }
}
