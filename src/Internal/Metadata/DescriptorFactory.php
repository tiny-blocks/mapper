<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper\Internal\Metadata;

use BackedEnum;
use DateTimeInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use TinyBlocks\Mapper\ElementType;
use TinyBlocks\Mapper\ScalarCodec;
use UnitEnum;

final class DescriptorFactory
{
    private function __construct()
    {
    }

    public static function build(string $type): ClassDescriptor
    {
        $kind = DescriptorFactory::kindOf(type: $type);
        $reflection = class_exists($type) ? new ReflectionClass(objectOrClass: $type) : null;
        $elementType = DescriptorFactory::elementTypeOf(reflection: $reflection);
        $scalarCodecs = DescriptorFactory::scalarCodecsOf(reflection: $reflection);
        $declaredProperties = Properties::collectDeclared(reflection: $reflection);
        $singleProperty = count($declaredProperties) === 1 ? array_values($declaredProperties)[0] : null;
        $isDecomposable = ShapeAnalyzer::isDecomposable(
            kind: $kind,
            type: $type,
            singleProperty: $singleProperty,
            declaredProperties: $declaredProperties
        );
        $isReducibleToScalar = ShapeAnalyzer::isReducibleToScalar(
            kind: $kind,
            type: $type,
            scalarCodecs: $scalarCodecs,
            singleProperty: $singleProperty
        );

        return new ClassDescriptor(
            kind: $kind,
            type: $type,
            reflection: $reflection,
            elementType: $elementType,
            scalarCodecs: $scalarCodecs,
            isDecomposable: $isDecomposable,
            singleProperty: $singleProperty,
            declaredProperties: $declaredProperties,
            isReducibleToScalar: $isReducibleToScalar
        );
    }

    private static function kindOf(string $type): Kind
    {
        return match (true) {
            is_subclass_of($type, BackedEnum::class)    => Kind::BACKED_ENUM,
            is_a($type, DateTimeInterface::class, true) => Kind::DATE_TIME,
            is_subclass_of($type, UnitEnum::class)      => Kind::PURE_ENUM,
            default                                     => Kind::OBJECT
        };
    }

    private static function bindingOf(string $type, ScalarCodec $codec): ScalarCodecBinding
    {
        $decodeType = (string) new ReflectionMethod(objectOrMethod: $type, method: $codec->decode)
            ->getParameters()[0]->getType();

        return new ScalarCodecBinding(decode: $codec->decode, encode: $codec->encode, decodeType: $decodeType);
    }

    private static function elementTypeOf(?ReflectionClass $reflection): ?string
    {
        if (is_null($reflection)) {
            return null;
        }

        $attributes = $reflection->getAttributes(ElementType::class);

        return $attributes === [] ? null : $attributes[0]->newInstance()->type;
    }

    private static function scalarCodecsOf(?ReflectionClass $reflection): array
    {
        if (is_null($reflection)) {
            return [];
        }

        $type = $reflection->getName();

        return array_map(
            static fn(ReflectionAttribute $attribute): ScalarCodecBinding => DescriptorFactory::bindingOf(
                type: $type,
                codec: $attribute->newInstance()
            ),
            $reflection->getAttributes(ScalarCodec::class)
        );
    }
}
