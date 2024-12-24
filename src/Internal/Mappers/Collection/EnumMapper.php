<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Collection;

use BackedEnum;
use UnitEnum;

final readonly class EnumMapper
{
    public function map(UnitEnum $value): mixed
    {
        return is_a($value, BackedEnum::class) ? $value->value : $value->name;
    }
}
