<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Exceptions;

use RuntimeException;

/**
 * Raised when a subtype value matches no case and no default factory is configured.
 */
final class UnknownSubtype extends RuntimeException
{
}
