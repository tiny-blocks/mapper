<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Shelf
{
    public function __construct(public string $name, public Catalog $catalog)
    {
    }
}
