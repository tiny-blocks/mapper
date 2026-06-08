<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * Defines whether properties whose value is null are kept or omitted when serializing objects.
 */
enum NullPreservation: string
{
    case KEEP = 'keep';
    case OMIT = 'omit';

    /**
     * Tells whether null-valued properties should be omitted.
     *
     * @return bool True if null-valued properties should be omitted, false otherwise.
     */
    public function shouldOmitNulls(): bool
    {
        return match ($this) {
            self::OMIT => true,
            self::KEEP => false
        };
    }
}
