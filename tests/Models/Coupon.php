<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Coupon
{
    public function __construct(public Ticket $ticket)
    {
    }
}
