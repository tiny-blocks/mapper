<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Workspace
{
    public function __construct(public string $label, public Profile $owner)
    {
    }
}
