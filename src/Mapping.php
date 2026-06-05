<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * Bidirectional rule that reads and writes a single type, overriding the default reflection.
 */
interface Mapping
{
    /**
     * Builds an instance from the source.
     *
     * @param mixed $source The source value for the mapped type.
     * @param MappingContext $context The mapping context, used to recurse into nested types.
     * @return object The built instance.
     */
    public function read(mixed $source, MappingContext $context): object;

    /**
     * Produces the portable representation of the instance.
     *
     * @param object $subject The instance to serialize.
     * @param MappingContext $context The mapping context, used to recurse into nested types.
     * @return mixed The portable representation, a scalar or an array.
     */
    public function write(object $subject, MappingContext $context): mixed;
}
