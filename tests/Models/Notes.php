<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use Generator;
use IteratorAggregate;
use TinyBlocks\Mapper\IterableMappable;
use TinyBlocks\Mapper\Mapper;

final readonly class Notes implements IteratorAggregate, IterableMappable
{
    private function __construct(public iterable $elements)
    {
    }

    public static function createFrom(iterable $elements): Notes
    {
        return new Notes(elements: $elements);
    }

    public function toJson(): string
    {
        return Mapper::create()->toJson(source: $this);
    }

    public function toArray(): array
    {
        return Mapper::create()->toArray(source: $this);
    }

    public function getIterator(): Generator
    {
        foreach ($this->elements as $key => $element) {
            yield $key => $element;
        }
    }
}
