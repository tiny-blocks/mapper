<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final class Tag implements ObjectMapper
{
    use ObjectMappability;

    public string $name = '';
    public string $color = 'gray';
}
