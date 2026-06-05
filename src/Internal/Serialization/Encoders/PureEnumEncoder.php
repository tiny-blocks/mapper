<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Serialization\Encoders;

use UnitEnum;
use TinyBlocks\Mapper\Internal\Metadata\ClassDescriptor;
use TinyBlocks\Mapper\Internal\Metadata\Kind;

final readonly class PureEnumEncoder implements Encoder
{
    public function encode(object $subject, ClassDescriptor $descriptor): mixed
    {
        assert($subject instanceof UnitEnum);

        return $subject->name;
    }

    public function supports(object $subject, ClassDescriptor $descriptor): bool
    {
        return $descriptor->kind === Kind::PURE_ENUM;
    }
}
