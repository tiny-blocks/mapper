<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * Builds the implementing type from a source, without an explicit {@see Mapper} instance.
 */
interface Deserializable
{
    /**
     * Builds the instance from the given source.
     *
     * @param string|iterable<int|string, mixed> $source The source data as a JSON string, an array, or an iterable.
     * @return static The built instance.
     */
    public static function buildFrom(string|iterable $source): static;
}
