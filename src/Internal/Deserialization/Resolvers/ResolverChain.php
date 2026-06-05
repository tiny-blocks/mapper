<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Deserialization\Resolvers;

use TinyBlocks\Mapper\Exceptions\UnmappableSource;
use TinyBlocks\Mapper\Internal\Metadata\ClassDescriptor;

final readonly class ResolverChain
{
    private array $resolvers;

    public function __construct(Resolver ...$resolvers)
    {
        $this->resolvers = $resolvers;
    }

    public function resolve(mixed $value, ClassDescriptor $descriptor): object
    {
        $resolver = array_find(
            $this->resolvers,
            static fn(Resolver $resolver): bool => $resolver->supports(value: $value, descriptor: $descriptor)
        );

        if (is_null($resolver)) {
            $template = 'Cannot resolve %s from value of type %s.';

            throw new UnmappableSource(message: sprintf($template, $descriptor->type, get_debug_type($value)));
        }

        return $resolver->resolve(value: $value, descriptor: $descriptor);
    }
}
