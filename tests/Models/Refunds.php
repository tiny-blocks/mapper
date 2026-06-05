<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use Generator;
use IteratorAggregate;
use TinyBlocks\Mapper\ElementType;
use TinyBlocks\Mapper\IterableMappable;
use TinyBlocks\Mapper\Mapper;

#[ElementType(Refund::class)]
readonly class Refunds implements IteratorAggregate, IterableMappable
{
    private function __construct(public iterable $elements)
    {
    }

    public static function createFrom(iterable $elements): static
    {
        return new static(elements: $elements);
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
