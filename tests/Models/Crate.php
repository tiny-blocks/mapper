<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Crate
{
    public function __construct(public Sku $sku)
    {
    }
}
