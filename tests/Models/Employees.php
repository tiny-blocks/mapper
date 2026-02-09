<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final class Employees extends Collection
{
    public function getType(): string
    {
        return Employee::class;
    }
}
