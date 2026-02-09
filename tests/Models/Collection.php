<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use Countable;
use TinyBlocks\Mapper\IterableMappability;
use TinyBlocks\Mapper\IterableMapper;

class Collection implements Countable, IterableMapper
{
    use IterableMappability;

    private function __construct(public readonly iterable $elements)
    {
    }

    public static function createFrom(iterable $elements): static
    {
        return new static(elements: $elements);
    }

    public function count(): int
    {
        if (is_array($this->elements)) {
            return count($this->elements);
        }

        $count = 0;

        foreach ($this->elements as $ignored) {
            $count++;
        }

        return $count;
    }
}
