<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappers\Object\Casters\Types;

use ReflectionEnum;
use TinyBlocks\Mapper\Internal\Exceptions\InvalidCast;
use TinyBlocks\Mapper\Internal\Mappers\Object\Casters\TypeCaster;
use UnitEnum;

final readonly class EnumCaster implements TypeCaster
{
    public function __construct(public string $class)
    {
    }

    public function applyCast(mixed $value): UnitEnum
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
