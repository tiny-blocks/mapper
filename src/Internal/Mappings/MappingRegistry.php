<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Mappings;

final class MappingRegistry
{
    private array $resolved = [];

    public function __construct(private readonly array $mappings)
    {
    }

    public function find(string $type): ?RegisteredMapping
    {
        if ($this->mappings === []) {
            return null;
        }

        if (!array_key_exists($type, $this->resolved)) {
            $this->resolved[$type] = $this->resolve(type: $type);
        }

        return $this->resolved[$type];
    }

    private function resolve(string $type): ?RegisteredMapping
    {
        if (array_key_exists($type, $this->mappings)) {
            return new RegisteredMapping(mapping: $this->mappings[$type], registeredType: $type);
        }

        foreach ($this->mappings as $registeredType => $mapping) {
            if (is_a($type, $registeredType, true)) {
                return new RegisteredMapping(mapping: $mapping, registeredType: $registeredType);
            }
        }

        return null;
    }
}
