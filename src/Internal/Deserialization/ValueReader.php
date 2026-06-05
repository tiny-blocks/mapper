<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Deserialization;

use ReflectionNamedType;
use ReflectionType;
use TinyBlocks\Mapper\Exceptions\UnmappableSource;
use TinyBlocks\Mapper\Internal\Deserialization\Resolvers\ResolverChain;
use TinyBlocks\Mapper\Internal\Deserialization\Resolvers\ResolverFactory;
use TinyBlocks\Mapper\Internal\Engine;
use TinyBlocks\Mapper\Internal\Metadata\ClassDescriptor;
use TinyBlocks\Mapper\Internal\Metadata\Descriptors;
use TinyBlocks\Mapper\Internal\Source;
use TinyBlocks\Mapper\IterableMappable;
use TinyBlocks\Mapper\NamingStrategy;

final readonly class ValueReader
{
    private ResolverChain $chain;

    private Hydrator $hydrator;

    public function __construct(private Engine $engine, NamingStrategy $naming, bool $rejectUnknownKeys)
    {
        $this->chain = ResolverFactory::build(engine: $engine, reader: $this);
        $this->hydrator = new Hydrator(
            naming: $naming,
            valueReader: $this,
            rejectUnknownKeys: $rejectUnknownKeys
        );
    }

    public function read(string $type, mixed $source): object
    {
        $descriptor = Descriptors::of(type: $type);

        if ($descriptor->hasScalarCodec()) {
            return $this->chain->resolve(value: $source, descriptor: $descriptor);
        }

        $normalized = Source::normalize(source: $source);

        if (is_a($type, IterableMappable::class, true)) {
            return $this->buildIterable(descriptor: $descriptor, source: $normalized);
        }

        return $this->hydrator->build(source: $normalized, descriptor: $descriptor);
    }

    public function resolve(mixed $value, ?ReflectionType $type): mixed
    {
        if (!$type instanceof ReflectionNamedType) {
            return $value;
        }

        if ($type->isBuiltin() || (is_null($value) && $type->allowsNull())) {
            return $value;
        }

        return $this->resolveClass(value: $value, class: $type->getName());
    }

    private function resolveClass(mixed $value, string $class): object
    {
        if ($value instanceof $class) {
            return $value;
        }

        if (is_null($value)) {
            $template = 'Cannot resolve %s from null.';

            throw new UnmappableSource(message: sprintf($template, $class));
        }

        if ($this->engine->hasMapping(type: $class)) {
            return $this->engine->read(type: $class, source: $value);
        }

        return $this->chain->resolve(value: $value, descriptor: Descriptors::of(type: $class));
    }

    private function buildIterable(ClassDescriptor $descriptor, array $source): object
    {
        $type = $descriptor->type;
        $elementType = $descriptor->elementType;

        $elements = is_null($elementType)
            ? $source
            : array_map(
                fn(mixed $element): object => $this->engine->read(type: $elementType, source: $element),
                $source
            );

        return $type::createFrom(elements: $elements);
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     * @return T
     */
    public function reflectionRead(string $type, array $source): object
    {
        return $this->hydrator->build(source: $source, descriptor: Descriptors::of(type: $type));
    }
}
