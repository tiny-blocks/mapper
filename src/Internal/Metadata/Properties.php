<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Metadata;

use ReflectionClass;

final class Properties
{
    private function __construct()
    {
    }

    public static function collectDeclared(?ReflectionClass $reflection): array
    {
        if (is_null($reflection)) {
            return [];
        }

        $collected = [];
        $current = $reflection;

        while ($current !== false) {
            foreach ($current->getProperties() as $property) {
                if ($property->isStatic()) {
                    continue;
                }

                if (array_key_exists($property->getName(), $collected)) {
                    continue;
                }

                $collected[$property->getName()] = $property;
            }

            $current = $current->getParentClass();
        }

        return $collected;
    }
}
