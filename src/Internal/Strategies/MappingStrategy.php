<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Strategies;

use TinyBlocks\Mapper\KeyPreservation;

/**
 * Defines the contract for mapping strategies.
 */
interface MappingStrategy
{
    /**
     * Maps a value to its array representation.
     *
     * @param mixed $value The value to map.
     * @param KeyPreservation $keyPreservation The key preservation strategy.
     * @return mixed The mapped value.
     */
    public function map(mixed $value, KeyPreservation $keyPreservation): mixed;

    /**
     * Checks if the strategy supports the given value.
     *
     * @param mixed $value The value to check.
     * @return bool True if supported, false otherwise.
     */
    public function supports(mixed $value): bool;

    /**
     * Returns the priority of this strategy (higher = checked first).
     *
     * @return int The priority value.
     */
    public function priority(): int;
}
