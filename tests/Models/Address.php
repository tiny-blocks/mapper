<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\Mappable;
use TinyBlocks\Mapper\MappableBehavior;

final readonly class Address implements Mappable
{
    use MappableBehavior;

    public function __construct(public string $city, public string $street)
    {
    }
}
