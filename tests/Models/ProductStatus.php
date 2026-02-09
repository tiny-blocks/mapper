<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

enum ProductStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 2;
}
