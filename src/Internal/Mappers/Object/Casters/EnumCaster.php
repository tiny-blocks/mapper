<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use BackedEnum;
use TinyBlocks\Mapper\Internal\Exceptions\InvalidCast;
use UnitEnum;

final readonly class EnumCaster implements Caster
{
    public function __construct(private string $class)
    {
    }

    public function castValue(mixed $value): UnitEnum
    {
        if ($value instanceof $this->class) {
            return $value;
        }

        if (is_subclass_of($this->class, BackedEnum::class)) {
            return ($this->class)::tryFrom($value)
                ?? throw InvalidCast::forEnumValue(value: $value, class: $this->class);
        }

        foreach (($this->class)::cases() as $case) {
            if ($case->name === $value) {
                return $case;
            }
        }

        throw InvalidCast::forEnumValue(value: $value, class: $this->class);
    }
}
