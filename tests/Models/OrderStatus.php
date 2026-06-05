<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

enum OrderStatus: string
{
    case PAID = 'paid';
    case PENDING = 'pending';
}
