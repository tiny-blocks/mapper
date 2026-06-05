<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Deserialization\Resolvers;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use TinyBlocks\Mapper\Exceptions\UnmappableSource;
use TinyBlocks\Mapper\Internal\Metadata\ClassDescriptor;
use TinyBlocks\Mapper\Internal\Metadata\Kind;

final readonly class DateTimeResolver implements Resolver
{
    public function resolve(mixed $value, ClassDescriptor $descriptor): DateTimeInterface
    {
        $class = $descriptor->type;
        $concrete = $class === DateTime::class ? DateTime::class : DateTimeImmutable::class;

        if (!is_int($value) && !is_string($value)) {
            $template = 'Cannot build %s from value of type %s.';

            throw new UnmappableSource(message: sprintf($template, $class, get_debug_type($value)));
        }

        $template = '@%d';
        $candidate = is_int($value) ? sprintf($template, $value) : $value;

        try {
            return new $concrete(datetime: $candidate);
        } catch (Exception $failure) {
            $template = 'Cannot build %s from "%s": %s.';

            throw new UnmappableSource(
                message: sprintf($template, $class, $candidate, $failure->getMessage()),
                previous: $failure
            );
        }
    }

    public function supports(mixed $value, ClassDescriptor $descriptor): bool
    {
        return $descriptor->kind === Kind::DATE_TIME;
    }
}
