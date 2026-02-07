<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use DateTimeImmutable;
use DateTimeInterface;

final readonly class DateTimeCaster implements Caster
{
    public function castValue(mixed $value): DateTimeInterface
    {
        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        return new DateTimeImmutable(datetime: $value);
    }
}
