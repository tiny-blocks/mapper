<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * Maps an iterable type: builds itself from an iterable of already-mapped elements.
 *
 * <p>Extends {@see Serializable} for the portable output. On read, the mapper maps each source element to
 * the element type declared with {@see ElementType} (absence means passthrough), then hands the elements to
 * {@see IterableMappable::createFrom()}.</p>
 */
interface IterableMappable extends Serializable
{
    /**
     * Creates the type from an iterable of already-mapped elements.
     *
     * @param iterable<int|string, mixed> $elements The already-mapped elements.
     * @return static The built instance.
     */
    public static function createFrom(iterable $elements): static;
}
