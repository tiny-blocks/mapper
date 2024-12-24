<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Collection;

use DateTimeInterface;

final readonly class DateTimeMapper
{
    private const int UTC_OFFSET = 0;

    public function map(DateTimeInterface $value): string
    {
        if ($value->getTimezone()->getOffset($value) !== self::UTC_OFFSET) {
            return $value->format(DateTimeInterface::ATOM);
        }

        return $value->format('Y-m-d H:i:s');
    }
}
