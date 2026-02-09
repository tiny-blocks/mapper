<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

enum Priority: int
{
    case LOW = 1;
    case MEDIUM = 2;
    case HIGH = 3;
}
