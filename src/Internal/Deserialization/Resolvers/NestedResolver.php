<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Deserialization\Resolvers;

use TinyBlocks\Mapper\Internal\Engine;
use TinyBlocks\Mapper\Internal\Metadata\ClassDescriptor;

final readonly class NestedResolver implements Resolver
{
    public function __construct(private Engine $engine)
    {
    }

    public function resolve(mixed $value, ClassDescriptor $descriptor): object
    {
        return $this->engine->read(type: $descriptor->type, source: $value);
    }

    public function supports(mixed $value, ClassDescriptor $descriptor): bool
    {
        return is_iterable($value);
    }
}
