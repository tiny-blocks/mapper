<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Payment
{
    public function __construct(public Order $order, public Charge $charge)
    {
    }
}
