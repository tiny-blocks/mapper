<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use Generator;

final readonly class GeneratorCaster implements Caster
{
    public function castValue(mixed $value): Generator
    {
        if (is_iterable(value: $value)) {
            foreach ($value as $item) {
                yield $item;
            }

            return;
        }

        yield $value;
    }
}
