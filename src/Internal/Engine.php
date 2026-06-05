<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal;

use TinyBlocks\Mapper\Configuration;
use TinyBlocks\Mapper\Identity;
use TinyBlocks\Mapper\Internal\Deserialization\FactoryInvoker;
use TinyBlocks\Mapper\Internal\Deserialization\ValueReader;
use TinyBlocks\Mapper\Internal\Mappings\Layout\LayoutCodec;
use TinyBlocks\Mapper\Internal\Mappings\MappingRegistry;
use TinyBlocks\Mapper\Internal\Mappings\SubtypeMapping;
use TinyBlocks\Mapper\Internal\Metadata\Descriptors;
use TinyBlocks\Mapper\Internal\Serialization\ValueWriter;
use TinyBlocks\Mapper\NamingStrategy;

final class Engine
{
    private static ?Engine $identity = null;

    private readonly MappingRegistry $registry;

    private readonly ValueReader $valueReader;

    private readonly ValueWriter $valueWriter;

    private readonly FactoryInvoker $factoryInvoker;

    public function __construct(array $mappings, private readonly NamingStrategy $strategy, bool $rejectUnknownKeys)
    {
        foreach ($mappings as $registeredType => $mapping) {
            if ($mapping instanceof SubtypeMapping) {
                $mapping->ensureCasesAreSubtypesOf(root: $registeredType);
            }
        }

        $this->registry = new MappingRegistry(mappings: $mappings);
        $this->valueReader = new ValueReader(
            engine: $this,
            naming: $this->strategy,
            rejectUnknownKeys: $rejectUnknownKeys
        );
        $this->valueWriter = new ValueWriter(engine: $this, naming: $this->strategy, registry: $this->registry);
        $this->factoryInvoker = new FactoryInvoker(naming: $this->strategy, valueReader: $this->valueReader);
    }

    public static function for(array $mappings, NamingStrategy $strategy, bool $rejectUnknownKeys): Engine
    {
        $isIdentity = $mappings === [] && $strategy instanceof Identity && !$rejectUnknownKeys;

        return $isIdentity
            ? Engine::identity()
            : new Engine(mappings: $mappings, strategy: $strategy, rejectUnknownKeys: $rejectUnknownKeys);
    }

    public static function identity(): Engine
    {
        return Engine::$identity ??= new Engine(
            mappings: [],
            strategy: Identity::create(),
            rejectUnknownKeys: false
        );
    }

    public function read(string $type, mixed $source): object
    {
        $registered = $this->registry->find(type: $type);

        if (!is_null($registered)) {
            return $registered->mapping->read(
                source: $source,
                context: new Context(
                    engine: $this,
                    targetType: $registered->registeredType,
                    configuration: Configuration::default()
                )
            );
        }

        return $this->valueReader->read(type: $type, source: $source);
    }

    public function write(mixed $value, Configuration $configuration): mixed
    {
        return $this->valueWriter->write(value: $value, configuration: $configuration);
    }

    public function strategy(): NamingStrategy
    {
        return $this->strategy;
    }

    public function arguments(string $type, array $paths, mixed $source): array
    {
        return new LayoutCodec(paths: $paths)->arguments(
            row: Source::normalize(source: $source),
            type: $type,
            naming: $this->strategy,
            valueReader: $this->valueReader
        );
    }

    public function serialize(object $subject, Configuration $configuration): mixed
    {
        return $this->valueWriter->serialize(subject: $subject, configuration: $configuration);
    }

    public function hasMapping(string $type): bool
    {
        return !is_null($this->registry->find(type: $type));
    }

    public function factoryRead(string $type, string $method, mixed $source): object
    {
        return $this->factoryInvoker->invoke(type: $type, method: $method, source: $source);
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     * @return T
     */
    public function reflectionRead(string $type, array $source): object
    {
        return $this->valueReader->reflectionRead(type: $type, source: $source);
    }

    public function reflectionWrite(object $subject, ?Configuration $configuration): array
    {
        return $this->valueWriter->reflectionWrite(
            subject: $subject,
            descriptor: Descriptors::of(type: $subject::class),
            configuration: $configuration ?? Configuration::default()
        );
    }
}
