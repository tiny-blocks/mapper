<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * Defines methods for converting iterable objects or collections of objects to JSON and arrays.
 */
interface IterableMapper extends Mapper
{
    /**
     * Get the type of the iterable collection of objects.
     *
     * @return string The type of the objects in the collection.
     */
    public function getType(): string;
}
