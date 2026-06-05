<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Owner
{
    public function __construct(public string $name, public MemberId $memberId)
    {
    }
}
