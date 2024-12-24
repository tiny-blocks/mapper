<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * Defines whether to preserve or discard keys during mapping or conversion.
 */
enum KeyPreservation
{
    /**
     * Indicates that the keys should be discarded.
     */
    case DISCARD;

    /**
     * Indicates that the keys should be preserved.
     */
    case PRESERVE;

    /**
     * Determines if keys should be preserved.
     *
     * @return bool Returns true if the keys should be preserved, false otherwise.
     */
    public function shouldPreserveKeys(): bool
    {
        return $this === self::PRESERVE;
    }
}
