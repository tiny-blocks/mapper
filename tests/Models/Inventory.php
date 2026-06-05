<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Inventory
{
    public function __construct(public array $stocks, public string $location)
    {
    }
}
