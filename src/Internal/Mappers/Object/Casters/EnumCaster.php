<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use ReflectionEnum;
use ReflectionEnumBackedCase;
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

        $reflection = new ReflectionEnum(objectOrClass: $this->class);

        foreach ($reflection->getCases() as $case) {
            $caseInstance = $case->getValue();

            if ($case instanceof ReflectionEnumBackedCase && $case->getBackingValue() === $value) {
                return $caseInstance;
            }

            if ($caseInstance->name === $value) {
                return $caseInstance;
            }
        }

        throw InvalidCast::forEnumValue(value: $value, class: $this->class);
    }
}
