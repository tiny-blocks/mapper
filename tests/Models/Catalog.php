<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\IterableMappability;
use TinyBlocks\Mapper\IterableMapper;

final readonly class Catalog implements IterableMapper
{
    use IterableMappability;

    public function __construct(public string $name, public array $items)
    {
    }
}
