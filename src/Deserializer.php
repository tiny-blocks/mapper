<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use TinyBlocks\Mapper\Exceptions\UnexpectedKey;
use TinyBlocks\Mapper\Exceptions\UnknownSubtype;
use TinyBlocks\Mapper\Exceptions\UnmappableSource;

/**
 * Builds objects from arrays, JSON strings, and iterables.
 */
interface Deserializer
{
    /**
     * Maps the source into an instance of the given type.
     *
     * <p>The source is an array, a JSON string, or any iterable. Resolution order for the type: a registered
     * {@see Mapping}, then {@see IterableMappable} element mapping, then reflection.</p>
     *
     * @template T of object
     * @param class-string<T> $type The target type to build.
     * @param string|iterable<int|string, mixed> $source The source data as a JSON string, an array, or an iterable.
     * @return T The reconstructed instance.
     * @throws UnmappableSource If the source cannot be mapped to the type.
     * @throws UnknownSubtype If a subtype value matches no case and no default.
     * @throws UnexpectedKey If a source key matches no property and the mapper rejects unknown keys.
     */
    public function toObject(string $type, string|iterable $source): object;

    /**
     * Resolves the source into a property-keyed map of built values, without instantiating the type.
     *
     * <p>Each declared property of the type is resolved through the same pipeline as a full read, honoring
     * registered mappings, nested graphs, and the active naming strategy. A property is present in the result
     * only when at least one of its backing columns exists in the source. A present column holding
     * <code>null</code> yields the property with a <code>null</code> value.</p>
     *
     * @param class-string $type The type whose property shape drives the resolution.
     * @param array<string, string|JsonColumn> $paths Map of dot-notation graph path to a column name, or to a
     *                                                 {@see JsonColumn} for a column holding a JSON document.
     * @param string|iterable<int|string, mixed> $source The flat source row as a JSON string, an array, or an iterable.
     * @return array<string, mixed> The resolved values keyed by property name.
     */
    public function toProperties(string $type, array $paths, string|iterable $source): array;

    /**
     * Maps the source into an instance of the given type, or null when the source is null.
     *
     * @template T of object
     * @param class-string<T> $type The target type to build.
     * @param string|iterable<int|string, mixed>|null $source The source data, or null.
     * @return T|null The reconstructed instance, or null when the source is null.
     * @throws UnmappableSource If a non-null source cannot be mapped to the type.
     * @throws UnknownSubtype If a subtype value matches no case and no default.
     * @throws UnexpectedKey If a source key matches no property and the mapper rejects unknown keys.
     */
    public function toObjectOrNull(string $type, string|iterable|null $source): ?object;
}
