<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Detectors;

use TinyBlocks\Collection\Collectible;

final readonly class CollectibleDetector implements TypeDetector
{
    public function matches(mixed $value): bool
    {
        return $value instanceof Collectible;
    }
}
