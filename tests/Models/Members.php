<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final class Members extends Collection
{
    public function getType(): string
    {
        return Member::class;
    }
}
