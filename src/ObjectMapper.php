<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use TinyBlocks\Mapper\Internal\Exceptions\InvalidCast;

/**
 * Defines methods for converting objects or collections of objects to JSON, arrays,
 * and for creating objects from iterables.
 */
interface ObjectMapper extends Mapper
{
    /**
     * Creates an object from an iterable.
     *
     * @param iterable $iterable The iterable to create the object from.
     * @return static The created object.
     * @throws InvalidCast If the iterable cannot be correctly cast to the object.
     */
    public static function fromIterable(iterable $iterable): static;
}
