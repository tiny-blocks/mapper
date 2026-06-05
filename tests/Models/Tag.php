<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

abstract class Tag
{
    public function __construct(public string $label)
    {
    }
}
