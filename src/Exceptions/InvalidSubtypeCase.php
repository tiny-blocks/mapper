<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Exceptions;

use LogicException;

/**
 * Raised when a subtype mapping is misconfigured, either because a case maps a class that is not a subtype
 * of the registered type, or because two types derive the same subtype value.
 */
final class InvalidSubtypeCase extends LogicException
{
}
