<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Serialization\Encoders;

use DateTimeInterface;
use TinyBlocks\Mapper\Internal\Metadata\ClassDescriptor;
use TinyBlocks\Mapper\Internal\Metadata\Kind;

final readonly class DateTimeEncoder implements Encoder
{
    public function encode(object $subject, ClassDescriptor $descriptor): mixed
    {
        assert($subject instanceof DateTimeInterface);

        return $subject->format(DateTimeInterface::ATOM);
    }

    public function supports(object $subject, ClassDescriptor $descriptor): bool
    {
        return $descriptor->kind === Kind::DATE_TIME;
    }
}
