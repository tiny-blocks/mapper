<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Metadata;

use ReflectionNamedType;
use ReflectionProperty;
use Traversable;

final class ShapeAnalyzer
{
    private function __construct()
    {
    }

    public static function isDecomposable(
        Kind $kind,
        string $type,
        ?ReflectionProperty $singleProperty,
        array $declaredProperties
    ): bool {
        $isLeaf = match (true) {
            is_a($type, Traversable::class, true),
                $kind === Kind::BACKED_ENUM => true,
            default                         => false
        };

        if ($isLeaf) {
            return false;
        }

        $count = count($declaredProperties);

        return match (true) {
            $count >= 2              => true,
            is_null($singleProperty) => false,
            default                  => ShapeAnalyzer::singleIsDecomposableClass(property: $singleProperty)
        };
    }

    private static function reducesToScalar(ReflectionProperty $property): bool
    {
        $type = $property->getType();

        if (!$type instanceof ReflectionNamedType) {
            return false;
        }

        return $type->isBuiltin() || Descriptors::of(type: $type->getName())->isReducibleToScalar;
    }

    public static function decomposableTypeOf(ReflectionProperty $property): ?string
    {
        $type = $property->getType();

        if (!$type instanceof ReflectionNamedType) {
            return null;
        }

        $class = $type->getName();

        return Descriptors::of(type: $class)->isDecomposable ? $class : null;
    }

    public static function isReducibleToScalar(
        Kind $kind,
        string $type,
        array $scalarCodecs,
        ?ReflectionProperty $singleProperty
    ): bool {
        if (is_a($type, Traversable::class, true)) {
            return false;
        }

        if ($kind !== Kind::OBJECT || $scalarCodecs !== []) {
            return true;
        }

        return !is_null($singleProperty) && ShapeAnalyzer::reducesToScalar(property: $singleProperty);
    }

    private static function singleIsDecomposableClass(ReflectionProperty $property): bool
    {
        $type = $property->getType();
        $isClass = $type instanceof ReflectionNamedType && !$type->isBuiltin();

        return $isClass && !ShapeAnalyzer::reducesToScalar(property: $property);
    }
}
