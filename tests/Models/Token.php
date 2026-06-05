<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Token
{
    public function __construct(public string|int $code)
    {
    }
}
