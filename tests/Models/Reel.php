<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use Generator;
use IteratorAggregate;

final readonly class Reel implements IteratorAggregate
{
    public function __construct(public iterable $frames)
    {
    }

    public function getIterator(): Generator
    {
        foreach ($this->frames as $key => $frame) {
            yield $key => $frame;
        }
    }
}
