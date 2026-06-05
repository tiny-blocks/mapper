<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Refund
{
    public function __construct(public Amount $amount, public string $reference)
    {
    }
}
