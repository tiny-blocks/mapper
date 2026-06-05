<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final class VersionedTag extends TraceableTag
{
    public function __construct(string $label, string $traceId, public int $version)
    {
        parent::__construct(label: $label, traceId: $traceId);
    }
}
