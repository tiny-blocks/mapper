<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Exceptions;

use RuntimeException;

/**
 * Raised when a source key matches no property and the mapper rejects unknown keys.
 */
final class UnexpectedKey extends RuntimeException
{
}
