<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

/**
 * Naming strategy where source keys already match property names.
 */
final readonly class Identity implements NamingStrategy
{
    private function __construct()
    {
    }

    /**
     * Creates an Identity naming strategy.
     *
     * @return Identity The new instance.
     */
    public static function create(): Identity
    {
        return new Identity();
    }

    public function toSourceKey(string $propertyName): string
    {
        return $propertyName;
    }

    public function derivedColumn(array $segments): string
    {
        $parts = [];

        foreach ($segments as $index => $segment) {
            $parts[] = $index === 0 ? $segment : ucfirst($segment);
        }

        return implode('', $parts);
    }
}
