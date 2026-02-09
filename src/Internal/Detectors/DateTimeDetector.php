<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Detectors;

use DateTimeInterface;

final readonly class DateTimeDetector implements TypeDetector
{
    public function matches(mixed $value): bool
    {
        return $value instanceof DateTimeInterface;
    }
}
