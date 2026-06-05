<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Deserialization\Resolvers;

use BackedEnum;
use TinyBlocks\Mapper\Exceptions\UnmappableSource;
use TinyBlocks\Mapper\Internal\Metadata\ClassDescriptor;
use TinyBlocks\Mapper\Internal\Metadata\Kind;

final readonly class BackedEnumResolver implements Resolver
{
    public function resolve(mixed $value, ClassDescriptor $descriptor): BackedEnum
    {
        /** @var class-string<BackedEnum> $class */
        $class = $descriptor->type;

        if (!is_int($value) && !is_string($value)) {
            $template = 'Cannot build %s from value of type %s.';

            throw new UnmappableSource(message: sprintf($template, $class, get_debug_type($value)));
        }

        return $class::from($value);
    }

    public function supports(mixed $value, ClassDescriptor $descriptor): bool
    {
        return $descriptor->kind === Kind::BACKED_ENUM;
    }
}
