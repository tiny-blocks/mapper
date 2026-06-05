<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use Closure;
use TinyBlocks\Mapper\Internal\Mappings\CodecMapping;

/**
 * Builds a Mapping that converts a scalar to and from an object using consumer-supplied closures.
 */
final class Codec
{
    private function __construct()
    {
    }

    /**
     * Creates a Mapping that decodes a scalar into an object and encodes it back to a scalar.
     *
     * <p>The codec keeps the library decoupled from the mapped type: the consumer owns both conversions. It is
     * registered like any other mapping through {@see Mapper::withMapping()}. The encode closure drives every write
     * of the type, nested or top-level. The decode closure drives the read when the type is mapped directly.</p>
     *
     * @param Closure $decode Builds the object from the scalar. Signature: <code>fn(string|int $raw): object</code>.
     * @param Closure $encode Reduces the object to a scalar. Signature: <code>fn(object $value): string|int</code>.
     * @return Mapping The configured mapping.
     */
    public static function from(Closure $decode, Closure $encode): Mapping
    {
        return new CodecMapping(decode: $decode, encode: $encode);
    }
}
