<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Serialization\Encoders;

use BackedEnum;
use TinyBlocks\Mapper\Internal\Metadata\ClassDescriptor;
use TinyBlocks\Mapper\Internal\Metadata\Kind;

final readonly class BackedEnumEncoder implements Encoder
{
    public function encode(object $subject, ClassDescriptor $descriptor): mixed
    {
        assert($subject instanceof BackedEnum);

        return $subject->value;
    }

    public function supports(object $subject, ClassDescriptor $descriptor): bool
    {
        return $descriptor->kind === Kind::BACKED_ENUM;
    }
}
