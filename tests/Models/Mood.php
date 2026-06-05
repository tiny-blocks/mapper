<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Mood
{
    public function __construct(public Severity $severity)
    {
    }
}
