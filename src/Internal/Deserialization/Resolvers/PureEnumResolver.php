<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Deserialization\Resolvers;

use UnitEnum;
use TinyBlocks\Mapper\Exceptions\UnmappableSource;
use TinyBlocks\Mapper\Internal\Metadata\ClassDescriptor;
use TinyBlocks\Mapper\Internal\Metadata\Kind;

final readonly class PureEnumResolver implements Resolver
{
    public function resolve(mixed $value, ClassDescriptor $descriptor): object
    {
        /** @var class-string<UnitEnum> $class */
        $class = $descriptor->type;
        $case = array_find($class::cases(), static fn(UnitEnum $case): bool => $case->name === $value);

        if (is_null($case)) {
            $template = 'No case named "%s" on pure enum %s.';

            throw new UnmappableSource(message: sprintf($template, (string) $value, $class));
        }

        return $case;
    }

    public function supports(mixed $value, ClassDescriptor $descriptor): bool
    {
        return $descriptor->kind === Kind::PURE_ENUM;
    }
}
