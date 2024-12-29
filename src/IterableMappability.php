<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use TinyBlocks\Mapper\Internal\Mappers\Collection\ArrayMapper;
use TinyBlocks\Mapper\Internal\Mappers\Json\JsonMapper;

trait IterableMappability
{
    public function toJson(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): string
    {
        $mapper = new JsonMapper();

        return $mapper->map(value: $this->toArray(keyPreservation: $keyPreservation));
    }

    public function toArray(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): array
    {
        $mapper = new ArrayMapper();

        return $mapper->map(value: $this, keyPreservation: $keyPreservation);
    }

    public function getType(): string
    {
        return static::class;
    }
}
