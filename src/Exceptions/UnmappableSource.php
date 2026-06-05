<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Exceptions;

use RuntimeException;

/**
 * Raised when a source value cannot be mapped to the requested type.
 */
final class UnmappableSource extends RuntimeException
{
}
