<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Strategies;

/**
 * A mapping strategy that can determine if it supports a given value.
 */
interface ConditionalMappingStrategy extends MappingStrategy
{
    /**
     * Checks if the strategy supports the given value.
     *
     * @param mixed $value The value to check.
     * @return bool True if supported, false otherwise.
     */
    public function supports(mixed $value): bool;
}
