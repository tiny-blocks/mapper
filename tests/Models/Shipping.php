<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final readonly class Shipping implements ObjectMapper
{
    use ObjectMappability;

    public function __construct(public int $id, public ShippingAddresses $addresses)
    {
    }
}
