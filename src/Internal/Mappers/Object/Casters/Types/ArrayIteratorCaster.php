<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters\Types;

use ArrayIterator;
use TinyBlocks\Mapper\Internal\Mappers\Object\Casters\TypeCaster;

final readonly class ArrayIteratorCaster implements TypeCaster
{
    public function applyCast(mixed $value): ArrayIterator
    {
        return new ArrayIterator($value);
    }
}
