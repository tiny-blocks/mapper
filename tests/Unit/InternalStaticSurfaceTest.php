<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use TinyBlocks\Mapper\Internal\Deserialization\Resolvers\ResolverFactory;
use TinyBlocks\Mapper\Internal\Json;
use TinyBlocks\Mapper\Internal\Metadata\DescriptorFactory;
use TinyBlocks\Mapper\Internal\Metadata\Descriptors;
use TinyBlocks\Mapper\Internal\Metadata\Properties;
use TinyBlocks\Mapper\Internal\Metadata\ShapeAnalyzer;
use TinyBlocks\Mapper\Internal\Serialization\Encoders\EncoderFactory;
use TinyBlocks\Mapper\Internal\Source;

final class InternalStaticSurfaceTest extends TestCase
{
    #[DataProvider('internalStaticSurfacesDataProvider')]
    public function testInternalStaticSurfaceWhenInspectedThenConstructorIsPrivate(string $staticSurface): void
    {
        /** @Given the private constructor of an internal static-only surface */
        $constructor = new ReflectionMethod(objectOrMethod: $staticSurface, method: '__construct');

        /** @When the constructor is invoked through reflection */
        $constructor->invoke(new ReflectionClass(objectOrClass: $staticSurface)->newInstanceWithoutConstructor());

        /** @Then the constructor is private to prevent direct instantiation */
        self::assertTrue($constructor->isPrivate());
    }

    public static function internalStaticSurfacesDataProvider(): array
    {
        return [
            'Json'              => [Json::class],
            'Source'            => [Source::class],
            'Properties'        => [Properties::class],
            'Descriptors'       => [Descriptors::class],
            'ShapeAnalyzer'     => [ShapeAnalyzer::class],
            'EncoderFactory'    => [EncoderFactory::class],
            'ResolverFactory'   => [ResolverFactory::class],
            'DescriptorFactory' => [DescriptorFactory::class]
        ];
    }
}
