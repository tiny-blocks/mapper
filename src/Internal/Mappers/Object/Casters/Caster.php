<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use TinyBlocks\Mapper\Internal\Exceptions\InvalidCast;

/**
 * Responsible for applying a cast to values, based on a specific type.
 */
interface Caster
{
    /**
     * Casts the given value to a specific type.
     *
     * @param mixed $value The value to be cast.
     * @return mixed The cast value.
     * @throws InvalidCast If the value cannot be cast to the expected type.
     */
    public function castValue(mixed $value): mixed;
}
