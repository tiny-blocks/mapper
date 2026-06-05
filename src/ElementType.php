<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use Attribute;

/**
 * Declares the element type an iterable type builds when the mapper maps it from a source.
 *
 * <p>PHP carries no runtime element type for an iterable, so an {@see IterableMappable} type declares it
 * here. On read, each source element is mapped to this type. Absence means passthrough: elements are kept
 * as-is, which is valid for generic iterables.</p>
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ElementType
{
    public function __construct(public string $type)
    {
    }
}
