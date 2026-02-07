<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Detectors;

final readonly class ScalarDetector implements TypeDetector
{
    public function matches(mixed $value): bool
    {
        return is_scalar($value);
    }
}
