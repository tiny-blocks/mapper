<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\KeyPreservation;
use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final readonly class Decimal implements ObjectMapper
{
    use ObjectMappability;

    public function __construct(public float $value)
    {
    }

    public function toArray(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): array
    {
        return ['value' => $this->value];
    }
}
