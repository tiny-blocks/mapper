<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Transformers;

/**
 * Transforms a value into a different representation as part of the mapping pipeline.
 *
 * <p>The verb <code>transform</code> is intentional. Value transformation is the operation
 * that motivates this library: every strategy, caster, resolver, and mapper exists to drive
 * concrete <code>Transformer</code> implementations. The anemic-verb prohibition from
 * <code>php-library-modeling.md</code> exempts verbs that constitute the library's reason to
 * exist; this method is the canonical case.</p>
 *
 * @see TinyBlocks\Mapper\Internal\Transformers\DateTimeTransformer
 * @see TinyBlocks\Mapper\Internal\Transformers\EnumTransformer
 * @see TinyBlocks\Mapper\Internal\Transformers\ValueObjectUnwrapper
 */
interface Transformer
{
    /**
     * Transforms the given value according to this transformer's contract.
     *
     * <p>Each implementation describes the specific transformation it performs (date
     * formatting, enum-to-scalar, value object unwrapping). The base contract guarantees
     * the input is not mutated.</p>
     *
     * @param mixed $value The value to transform.
     * @return mixed The transformed value, as the concrete implementation defines it.
     */
    public function transform(mixed $value): mixed;
}
