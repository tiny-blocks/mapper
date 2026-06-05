<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\Mappable;
use TinyBlocks\Mapper\MappableBehavior;

final readonly class Wallet implements Mappable
{
    use MappableBehavior;

    public function __construct(public Amount $balance)
    {
    }
}
