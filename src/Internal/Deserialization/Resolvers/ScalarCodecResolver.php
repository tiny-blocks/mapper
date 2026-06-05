<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Deserialization\Resolvers;

use TinyBlocks\Mapper\Exceptions\UnmappableSource;
use TinyBlocks\Mapper\Internal\Metadata\ClassDescriptor;

final readonly class ScalarCodecResolver implements Resolver
{
    public function resolve(mixed $value, ClassDescriptor $descriptor): object
    {
        $type = $descriptor->type;
        $codec = array_find(
            $descriptor->scalarCodecs,
            static fn(mixed $codec): bool => $codec->decodeType === get_debug_type($value)
        );

        if (is_null($codec)) {
            $template = 'Cannot build %s from value of type %s.';

            throw new UnmappableSource(message: sprintf($template, $type, get_debug_type($value)));
        }

        return $type::{$codec->decode}($value);
    }

    public function supports(mixed $value, ClassDescriptor $descriptor): bool
    {
        return $descriptor->hasScalarCodec();
    }
}
