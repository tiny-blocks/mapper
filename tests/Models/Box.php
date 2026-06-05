<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Box
{
    public function __construct(public Token $token)
    {
    }
}
