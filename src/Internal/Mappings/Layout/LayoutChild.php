<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappings\Layout;

use ReflectionProperty;

final readonly class LayoutChild
{
    public function __construct(public LayoutNode $node, public ReflectionProperty $property, public string $sourceKey)
    {
    }
}
