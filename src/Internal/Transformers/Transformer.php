<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Transformers;

/**
 * Defines the contract for value transformation strategies.
 */
interface Transformer
{
    /**
     * Transforms a value according to the implementation strategy.
     *
     * @param mixed $value The value to transform.
     * @return mixed The transformed value.
     */
    public function transform(mixed $value): mixed;
}
