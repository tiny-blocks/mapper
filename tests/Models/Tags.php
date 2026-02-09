<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final class Tags extends Collection
{
    public function getType(): string
    {
        return Tag::class;
    }
}
