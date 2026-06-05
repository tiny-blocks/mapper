<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use TinyBlocks\Mapper\Exceptions\UnknownSubtype;

/**
 * Serializes objects to portable arrays and JSON.
 */
interface Serializer
{
    /**
     * Returns the given object as a JSON string.
     *
     * @param object $source The instance to serialize.
     * @param Configuration|null $configuration Per-call output options. Defaults to {@see Configuration::default()}.
     * @return string The JSON representation.
     * @throws UnknownSubtype If the concrete class matches no subtype case.
     */
    public function toJson(object $source, ?Configuration $configuration = null): string;

    /**
     * Returns the given object as an array.
     *
     * @param object $source The instance to serialize.
     * @param Configuration|null $configuration Per-call output options. Defaults to {@see Configuration::default()}.
     * @return array<int|string, mixed> The array representation, with nested objects and collections resolved.
     * @throws UnknownSubtype If the concrete class matches no subtype case.
     */
    public function toArray(object $source, ?Configuration $configuration = null): array;

    /**
     * Returns the given object as a JSON string, or null when the source is null.
     *
     * @param object|null $source The instance to serialize, or null.
     * @param Configuration|null $configuration Per-call output options. Defaults to {@see Configuration::default()}.
     * @return string|null The JSON representation, or null when the source is null.
     * @throws UnknownSubtype If a non-null source's concrete class matches no subtype case.
     */
    public function toJsonOrNull(?object $source, ?Configuration $configuration = null): ?string;

    /**
     * Returns the given object as an array, or null when the source is null.
     *
     * @param object|null $source The instance to serialize, or null.
     * @param Configuration|null $configuration Per-call output options. Defaults to {@see Configuration::default()}.
     * @return array<int|string, mixed>|null The array representation, or null when the source is null.
     * @throws UnknownSubtype If a non-null source's concrete class matches no subtype case.
     */
    public function toArrayOrNull(?object $source, ?Configuration $configuration = null): ?array;
}
