<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters;

use ReflectionEnum;
use TinyBlocks\Mapper\Internal\Exceptions\InvalidCast;
use UnitEnum;

final readonly class EnumCaster implements Caster
{
    public function __construct(public string $class)
    {
    }

    public function castValue(mixed $value): UnitEnum
    {
        $reflectionEnum = new ReflectionEnum($this->class);

        foreach ($reflectionEnum->getCases() as $case) {
            $caseInstance = $case->getValue();

            if ($case->getEnum()->isBacked() && $case->getBackingValue() === $value) {
                return $caseInstance;
            }

            if ($caseInstance->name === $value) {
                return $caseInstance;
            }
        }

        throw InvalidCast::forEnumValue(value: $value, class: $this->class);
    }
}
