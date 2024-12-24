<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters\Types;

use DateTimeImmutable;
use TinyBlocks\Mapper\Internal\Mappers\Object\Casters\TypeCaster;

final readonly class DateTimeCaster implements TypeCaster
{
    public function applyCast(mixed $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value);
    }
}
