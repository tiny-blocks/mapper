<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Order
{
    public function __construct(public Amount $amount)
    {
    }
}
