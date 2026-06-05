<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappings;

use TinyBlocks\Mapper\Mapping;

final readonly class RegisteredMapping
{
    public function __construct(public Mapping $mapping, public string $registeredType)
    {
    }
}
