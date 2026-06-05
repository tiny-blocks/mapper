<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Deserialization\Resolvers;

use TinyBlocks\Mapper\Exceptions\UnmappableSource;
use TinyBlocks\Mapper\Internal\Deserialization\ValueReader;
use TinyBlocks\Mapper\Internal\Metadata\ClassDescriptor;

final readonly class SinglePropertyResolver implements Resolver
{
    public function __construct(private ValueReader $reader)
    {
    }

    public function resolve(mixed $value, ClassDescriptor $descriptor): object
    {
        $single = $descriptor->singleProperty;

        if (is_null($single)) {
            $template = 'Cannot resolve %s from scalar value of type %s without a single declared property.';

            throw new UnmappableSource(
                message: sprintf($template, $descriptor->type, get_debug_type($value))
            );
        }

        $instance = $descriptor->newInstance();
        $single->setValue($instance, $this->reader->resolve(value: $value, type: $single->getType()));

        return $instance;
    }

    public function supports(mixed $value, ClassDescriptor $descriptor): bool
    {
        return is_scalar($value);
    }
}
