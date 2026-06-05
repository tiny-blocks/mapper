<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use TinyBlocks\Mapper\Internal\Engine;
use TinyBlocks\Mapper\Internal\Json;

/**
 * Maps objects to and from arrays and JSON without coupling the mapped class to the library.
 *
 * <p>An immutable builder: {@see Mapper::create()} starts an empty mapper, and each <code>with*</code> method
 * returns a configured copy. A single internal engine backs this facade and the {@see MappableBehavior}
 * trait. Fulfills the {@see Serializer} and {@see Deserializer} service contracts.</p>
 */
final readonly class Mapper implements Serializer, Deserializer
{
    private Engine $engine;

    private function __construct(
        private array $mappings,
        private NamingStrategy $namingStrategy,
        private bool $rejectUnknownKeys
    ) {
        $this->engine = Engine::for(
            mappings: $this->mappings,
            strategy: $this->namingStrategy,
            rejectUnknownKeys: $this->rejectUnknownKeys
        );
    }

    /**
     * Creates an empty mapper with identity naming and lenient unknown keys.
     *
     * @return Mapper The new instance.
     */
    public static function create(): Mapper
    {
        return new Mapper(mappings: [], namingStrategy: Identity::create(), rejectUnknownKeys: false);
    }

    public function toJson(object $source, ?Configuration $configuration = null): string
    {
        return Json::encode(value: $this->toArray(source: $source, configuration: $configuration));
    }

    public function toArray(object $source, ?Configuration $configuration = null): array
    {
        $written = $this->engine->write(value: $source, configuration: $configuration ?? Configuration::default());

        return is_array($written) ? $written : [$written];
    }

    public function toJsonOrNull(?object $source, ?Configuration $configuration = null): ?string
    {
        return is_null($source) ? null : $this->toJson(source: $source, configuration: $configuration);
    }

    public function toArrayOrNull(?object $source, ?Configuration $configuration = null): ?array
    {
        return is_null($source) ? null : $this->toArray(source: $source, configuration: $configuration);
    }

    public function toObject(string $type, string|iterable $source): object
    {
        return $this->engine->read(type: $type, source: $source);
    }

    public function toProperties(string $type, array $paths, string|iterable $source): array
    {
        return $this->engine->arguments(type: $type, paths: $paths, source: $source);
    }

    public function toObjectOrNull(string $type, string|iterable|null $source): ?object
    {
        return is_null($source) ? null : $this->toObject(type: $type, source: $source);
    }

    /**
     * Returns a copy using the given naming strategy.
     *
     * @param NamingStrategy $namingStrategy The strategy to apply.
     * @return Mapper The new instance.
     */
    public function withNaming(NamingStrategy $namingStrategy): Mapper
    {
        return new Mapper(
            mappings: $this->mappings,
            namingStrategy: $namingStrategy,
            rejectUnknownKeys: $this->rejectUnknownKeys
        );
    }

    /**
     * Returns a copy with the given mapping registered for the type.
     *
     * @param class-string $type The type the mapping applies to.
     * @param Mapping $mapping The mapping, built from {@see Codec}, {@see FactoryMethod}, {@see Subtype},
     *                         {@see Layout}, or a custom {@see Mapping}.
     * @return Mapper The new instance.
     */
    public function withMapping(string $type, Mapping $mapping): Mapper
    {
        return new Mapper(
            mappings: [...$this->mappings, $type => $mapping],
            namingStrategy: $this->namingStrategy,
            rejectUnknownKeys: $this->rejectUnknownKeys
        );
    }

    /**
     * Returns a copy that fails on source keys matching no property, instead of ignoring them.
     *
     * @return Mapper The new instance.
     */
    public function rejectingUnknownKeys(): Mapper
    {
        return new Mapper(mappings: $this->mappings, namingStrategy: $this->namingStrategy, rejectUnknownKeys: true);
    }
}
