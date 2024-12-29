<?php

namespace TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final readonly class Merchant implements ObjectMapper
{
    use ObjectMappability;

    public function __construct(public string $id, public Stores $stores)
    {
    }
}
