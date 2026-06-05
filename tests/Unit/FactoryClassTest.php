<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use TinyBlocks\Mapper\Codec;
use TinyBlocks\Mapper\FactoryMethod;
use TinyBlocks\Mapper\Layout;
use TinyBlocks\Mapper\Subtype;

final class FactoryClassTest extends TestCase
{
    #[DataProvider('staticFactoryClassesDataProvider')]
    public function testStaticFactoryClassWhenInspectedThenConstructorIsPrivate(string $factoryClass): void
    {
        /** @Given the private constructor of a static-only factory class */
        $constructor = new ReflectionMethod(objectOrMethod: $factoryClass, method: '__construct');

        /** @When the constructor is invoked through reflection */
        $constructor->invoke(new ReflectionClass(objectOrClass: $factoryClass)->newInstanceWithoutConstructor());

        /** @Then the constructor is private to prevent direct instantiation */
        self::assertTrue($constructor->isPrivate());
    }

    public static function staticFactoryClassesDataProvider(): array
    {
        return [
            'Codec'         => [Codec::class],
            'Layout'        => [Layout::class],
            'Subtype'       => [Subtype::class],
            'FactoryMethod' => [FactoryMethod::class]
        ];
    }
}
