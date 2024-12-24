<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use TinyBlocks\Mapper\Internal\Exceptions\InvalidCast;

/**
 * Responsible for applying a cast to values, based on a specific type.
 */
interface TypeCaster
{
    /**
     * Applies a cast to the provided value.
     *
     * @param mixed $value The value to be cast.
     * @return mixed The cast value.
     * @throws InvalidCast Thrown when the value cannot be cast to the expected type.
     */
    public function applyCast(mixed $value): mixed;
}
