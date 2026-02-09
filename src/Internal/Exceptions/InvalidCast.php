<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Exceptions;

use InvalidArgumentException;

final class InvalidCast extends InvalidArgumentException
{
    public static function forEnumValue(int|string $value, string $class): InvalidCast
    {
        $message = sprintf('Invalid value <%s> for enum <%s>.', $value, $class);
        return new InvalidCast(message: $message);
    }
}
