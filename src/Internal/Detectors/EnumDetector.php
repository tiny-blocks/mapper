<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Detectors;

use UnitEnum;

final readonly class EnumDetector implements TypeDetector
{
    public function matches(mixed $value): bool
    {
        return $value instanceof UnitEnum;
    }
}
