<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final class Tracker
{
    public static int $sessions = 0;

    public function __construct(public int $hits, public string $name)
    {
    }
}
