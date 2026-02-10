<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use IteratorAggregate;
use TinyBlocks\Mapper\IterableMappability;
use TinyBlocks\Mapper\IterableMapper;
use Traversable;

final class InvoiceSummaries implements IterableMapper, IteratorAggregate
{
    use IterableMappability;

    private function __construct(private readonly Invoices $invoices)
    {
    }

    public static function createFrom(Invoices $invoices): InvoiceSummaries
    {
        return new InvoiceSummaries(invoices: $invoices);
    }

    public function getIterator(): Traversable
    {
        foreach ($this->invoices as $invoice) {
            yield $invoice->id => $invoice;
        }
    }
}
