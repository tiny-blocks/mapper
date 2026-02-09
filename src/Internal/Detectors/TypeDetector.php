<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Detectors;

/**
 * Defines the contract for type detection strategies.
 */
interface TypeDetector
{
    /**
     * Determines if the given value matches the detector's type criteria.
     *
     * @param mixed $value The value to detect.
     * @return bool True if the value matches, false otherwise.
     */
    public function matches(mixed $value): bool;
}
