<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Amount
{
    public function __construct(public int $amount, public Currency $currency)
    {
    }
}
