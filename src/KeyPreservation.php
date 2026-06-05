<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * Defines whether array keys are preserved or discarded when serializing iterable content.
 */
enum KeyPreservation: string
{
    case DISCARD = 'discard';
    case PRESERVE = 'preserve';

    /**
     * Tells whether keys should be preserved.
     *
     * @return bool True if keys should be preserved, false otherwise.
     */
    public function shouldPreserveKeys(): bool
    {
        return match ($this) {
            self::PRESERVE => true,
            self::DISCARD  => false
        };
    }
}
