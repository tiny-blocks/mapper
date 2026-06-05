<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * Naming strategy translating between snake_case source keys and camelCase property names.
 */
final readonly class SnakeCase implements NamingStrategy
{
    private function __construct()
    {
    }

    /**
     * Creates a SnakeCase naming strategy.
     *
     * @return SnakeCase The new instance.
     */
    public static function create(): SnakeCase
    {
        return new SnakeCase();
    }

    public function toSourceKey(string $propertyName): string
    {
        return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $propertyName) ?? $propertyName);
    }

    public function derivedColumn(array $segments): string
    {
        $parts = array_map(fn(string $segment): string => $this->toSourceKey(propertyName: $segment), $segments);

        return implode('_', $parts);
    }
}
