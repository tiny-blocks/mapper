<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Mapper\ObjectMappability;

final class Webhook
{
    use ObjectMappability;

    public static int $timeout = 30;

    public string $url = '';
    public bool $active = false;
}
