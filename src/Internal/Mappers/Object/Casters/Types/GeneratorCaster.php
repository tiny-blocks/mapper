<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters\Types;

use Generator;
use TinyBlocks\Mapper\Internal\Mappers\Object\Casters\TypeCaster;

final readonly class GeneratorCaster implements TypeCaster
{
    public function applyCast(mixed $value): Generator
    {
        if (is_iterable($value)) {
            foreach ($value as $item) {
                yield $item;
            }

            return;
        }

        yield $value;
    }
}
