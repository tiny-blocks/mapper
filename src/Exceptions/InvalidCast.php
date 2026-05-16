<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Exceptions;

use InvalidArgumentException;

/**
 * Signals that a scalar value could not be cast to the expected target type during mapping.
 */
final class InvalidCast extends InvalidArgumentException
{
    /**
     * Creates an InvalidCast describing an enum value that has no matching case.
     *
     * @param int|string $value The offending value rejected by the enum.
     * @param string $class The fully qualified name of the enum class.
     * @return InvalidCast The created exception with a descriptive message.
     */
    public static function forEnumValue(int|string $value, string $class): InvalidCast
    {
        $template = 'Invalid value <%s> for enum <%s>.';

        return new InvalidCast(message: sprintf($template, $value, $class));
    }
}
