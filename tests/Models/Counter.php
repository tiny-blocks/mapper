<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final class Counter
{
    public static int $instances = 0;

    public function __construct(public string $label, public int $value)
    {
    }
}
