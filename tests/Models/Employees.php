<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Collection\Collection;
use TinyBlocks\Mapper\IterableMappability;
use TinyBlocks\Mapper\IterableMapper;

final class Employees extends Collection implements IterableMapper
{
    use IterableMappability;

    public function getType(): string
    {
        return Employee::class;
    }
}
