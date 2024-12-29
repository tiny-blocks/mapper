<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use DateTimeImmutable;

final readonly class DateTimeCaster implements Caster
{
    public function castValue(mixed $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value);
    }
}
