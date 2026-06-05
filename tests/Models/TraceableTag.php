<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

abstract class TraceableTag
{
    public function __construct(public string $label, private string $traceId)
    {
    }

    public function traceId(): string
    {
        return $this->traceId;
    }
}
