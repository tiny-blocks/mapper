<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * Recursion and naming handed to a {@see Mapping} during a mapping operation.
 */
interface MappingContext
{
    /**
     * Maps the source into an instance of the given type, applying the full pipeline.
     *
     * @template T of object
     * @param class-string<T> $type The nested type to build.
     * @param mixed $source The source value for the nested type.
     * @return T The built instance.
     */
    public function read(string $type, mixed $source): object;

    /**
     * Serializes a value of any type through the active pipeline.
     *
     * @param mixed $value The value to serialize, scalar or object.
     * @return mixed The portable representation.
     */
    public function write(mixed $value): mixed;

    /**
     * Returns the active naming strategy.
     *
     * @return NamingStrategy The strategy in effect.
     */
    public function naming(): NamingStrategy;
}
