<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Holder
{
    public function __construct(public ?MemberId $memberId)
    {
    }
}
