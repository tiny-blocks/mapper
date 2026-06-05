<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use TinyBlocks\Mapper\Internal\Engine;
use TinyBlocks\Mapper\Internal\Json;
use TinyBlocks\Mapper\Internal\Source;

trait MappableBehavior
{
    public function toJson(?Configuration $configuration = null): string
    {
        return Json::encode(value: $this->toArray(configuration: $configuration));
    }

    public function toArray(?Configuration $configuration = null): array
    {
        return Engine::identity()->reflectionWrite(subject: $this, configuration: $configuration);
    }

    public static function buildFrom(string|iterable $source): static
    {
        return Engine::identity()->reflectionRead(type: static::class, source: Source::normalize(source: $source));
    }
}
