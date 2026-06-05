<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final class SpecializedTag extends Tag
{
    public function __construct(string $label, public int $level)
    {
        parent::__construct(label: $label);
    }
}
