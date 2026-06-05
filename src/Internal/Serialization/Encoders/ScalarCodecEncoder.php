<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Serialization\Encoders;

use TinyBlocks\Mapper\Internal\Metadata\ClassDescriptor;

final readonly class ScalarCodecEncoder implements Encoder
{
    public function encode(object $subject, ClassDescriptor $descriptor): mixed
    {
        return $subject->{$descriptor->scalarCodecs[0]->encode}();
    }

    public function supports(object $subject, ClassDescriptor $descriptor): bool
    {
        return $descriptor->hasScalarCodec();
    }
}
