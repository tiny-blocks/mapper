<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Pulse
{
    public function __construct(public OrderStatus $status)
    {
    }
}
