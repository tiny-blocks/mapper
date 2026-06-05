<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Deserialization\Resolvers;

use TinyBlocks\Mapper\Internal\Metadata\ClassDescriptor;

/**
 * Resolves a source value into an instance of the type a class descriptor describes.
 */
interface Resolver
{
    /**
     * Resolves the source value into an instance of the described type.
     *
     * @param mixed $value The source value to resolve.
     * @param ClassDescriptor $descriptor The descriptor of the target type.
     * @return object The resolved instance.
     */
    public function resolve(mixed $value, ClassDescriptor $descriptor): object;

    /**
     * Tells whether this resolver handles the given value for the described type.
     *
     * @param mixed $value The source value to inspect.
     * @param ClassDescriptor $descriptor The descriptor of the target type.
     * @return bool True when this resolver can resolve the value, false otherwise.
     */
    public function supports(mixed $value, ClassDescriptor $descriptor): bool;
}
