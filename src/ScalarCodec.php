<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use Attribute;

/**
 * Declares how a type converts to and from a scalar, by naming the methods the mapper calls.
 *
 * <p>Each attribute is one decode and encode pair, and the attribute is repeatable. On read, the pair whose
 * decode parameter type accepts the source scalar is used, so a type can be built from more than one scalar
 * form. On write, the first declared pair's encode is used. A registered mapping for the same type takes
 * precedence over this attribute.</p>
 *
 * <p>The decode method is a public static factory that builds the type from the scalar, and the encode method
 * a public instance method that reduces the type back to a scalar.</p>
 *
 * @see Codec for the registered, closure-based equivalent.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class ScalarCodec
{
    public function __construct(public string $decode, public string $encode)
    {
    }
}
