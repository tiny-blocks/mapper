<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use ArrayIterator;

final readonly class ArrayIteratorCaster implements Caster
{
    public function castValue(mixed $value): ArrayIterator
    {
        return new ArrayIterator(array: $value);
    }
}
