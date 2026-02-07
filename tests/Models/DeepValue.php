<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\ObjectMappability;

final readonly class DeepValue
{
    use ObjectMappability;

    public function __construct(public mixed $value)
    {
    }
}
