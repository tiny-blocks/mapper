<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use Closure;

final readonly class ClosureCaster implements Caster
{
    public function castValue(mixed $value): Closure
    {
        return static fn(): mixed => $value;
    }
}
