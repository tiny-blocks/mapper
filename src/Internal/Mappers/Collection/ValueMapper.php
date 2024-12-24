<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Collection;

use DateTimeInterface;
use TinyBlocks\Collection\Collectible;
use TinyBlocks\Mapper\KeyPreservation;
use UnitEnum;

final readonly class ValueMapper
{
    public function map(mixed $value, KeyPreservation $keyPreservation): mixed
    {
        return match (true) {
            is_a($value, UnitEnum::class)          => (new EnumMapper())->map(value: $value),
            is_a($value, DateTimeInterface::class) => (new DateTimeMapper())->map(value: $value),
            is_object($value)                      => (new ArrayMapper())->map(
                value: $value,
                keyPreservation: $keyPreservation
            ),
            default                                => $value
        };
    }

    public function valueIsCollectible(object $value): bool
    {
        return is_a($value, Collectible::class);
    }
}
