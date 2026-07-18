<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use Attribute;

/**
 * Marks a property the mapper skips on both write and read.
 *
 * <p>A transient property never appears in the serialized representation and is never hydrated from a
 * source document, so infrastructure state (event buffers, version counters, caches) stays out of the
 * portable form while remaining an ordinary property of the class.</p>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Transient
{
}
