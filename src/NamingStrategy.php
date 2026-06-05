<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * Translates source keys to property names, and derives nested column names by prefix.
 */
interface NamingStrategy
{
    /**
     * Translates a property name to its source key.
     *
     * @param string $propertyName The property name to translate.
     * @return string The source key under this convention.
     */
    public function toSourceKey(string $propertyName): string;

    /**
     * Derives a flat column name from the ordered property-path segments.
     *
     * @param array<int, string> $segments The property-path segments, from outermost to innermost.
     * @return string The derived column name under this convention.
     */
    public function derivedColumn(array $segments): string;
}
