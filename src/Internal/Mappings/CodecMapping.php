<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappings;

use Closure;
use TinyBlocks\Mapper\Mapping;
use TinyBlocks\Mapper\MappingContext;

final readonly class CodecMapping implements Mapping
{
    public function __construct(private Closure $decode, private Closure $encode)
    {
    }

    public function read(mixed $source, MappingContext $context): object
    {
        return ($this->decode)($source);
    }

    public function write(object $subject, MappingContext $context): mixed
    {
        return ($this->encode)($subject);
    }
}
