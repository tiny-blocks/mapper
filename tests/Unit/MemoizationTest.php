<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\SpecializedTag;
use Test\TinyBlocks\Mapper\Models\Tag;
use TinyBlocks\Mapper\Internal\Engine;
use TinyBlocks\Mapper\Internal\Mappings\Layout\LayoutCodec;
use TinyBlocks\Mapper\Internal\Mappings\MappingRegistry;
use TinyBlocks\Mapper\Internal\Mappings\RegisteredMapping;
use TinyBlocks\Mapper\Internal\Metadata\Descriptors;
use TinyBlocks\Mapper\Layout;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Mapper\SnakeCase;

final class MemoizationTest extends TestCase
{
    public function testFindWhenMappingsAreEmptyThenTheMissIsNotMemoized(): void
    {
        /** @Given a registry built without any mappings */
        $registry = new MappingRegistry(mappings: []);

        /** @And a reader for its memoized lookups */
        $resolved = new ReflectionProperty(class: MappingRegistry::class, property: 'resolved');

        /** @When a lookup misses */
        $found = $registry->find(type: Tag::class);

        /** @Then no mapping is returned */
        self::assertNull($found);

        /** @And the miss is not memoized */
        self::assertEmpty($resolved->getValue($registry));
    }

    public function testCreateWhenInvokedTwiceThenBothShareTheIdentityEngine(): void
    {
        /** @Given a reader for the engine backing the Mapper facade */
        $engineProperty = new ReflectionProperty(class: Mapper::class, property: 'engine');

        /** @When invoked twice */
        $first = $engineProperty->getValue(Mapper::create());
        $second = $engineProperty->getValue(Mapper::create());

        /** @Then both invocations expose the shared identity engine */
        self::assertSame(Engine::identity(), $first);

        /** @And the second invocation exposes that same instance */
        self::assertSame(Engine::identity(), $second);
    }

    public function testCreateWhenNamingIsSnakeCaseThenADedicatedEngineIsBuilt(): void
    {
        /** @Given a snake_case mapper */
        $mapper = Mapper::create()->withNaming(namingStrategy: SnakeCase::create());

        /** @When its backing engine is read */
        $engine = new ReflectionProperty(class: Mapper::class, property: 'engine')->getValue($mapper);

        /** @Then a dedicated engine is built instead of the shared identity one */
        self::assertNotSame(Engine::identity(), $engine);
    }

    public function testCreateWhenAMappingIsRegisteredThenADedicatedEngineIsBuilt(): void
    {
        /** @Given a mapper with a registered mapping */
        $mapper = Mapper::create()->withMapping(type: Amount::class, mapping: Layout::from(paths: []));

        /** @When its backing engine is read */
        $engine = new ReflectionProperty(class: Mapper::class, property: 'engine')->getValue($mapper);

        /** @Then a dedicated engine is built instead of the shared identity one */
        self::assertNotSame(Engine::identity(), $engine);
    }

    public function testCreateWhenRejectingUnknownKeysThenADedicatedEngineIsBuilt(): void
    {
        /** @Given a mapper that rejects unknown keys */
        $mapper = Mapper::create()->rejectingUnknownKeys();

        /** @When its backing engine is read */
        $engine = new ReflectionProperty(class: Mapper::class, property: 'engine')->getValue($mapper);

        /** @Then a dedicated engine is built instead of the shared identity one */
        self::assertNotSame(Engine::identity(), $engine);
    }

    public function testIdentityEngineWhenInvokedTwiceThenTheSameInstanceIsReturned(): void
    {
        /** @When invoked twice */
        $first = Engine::identity();
        $second = Engine::identity();

        /** @Then the same engine instance is returned */
        self::assertSame($first, $second);
    }

    public function testDescriptorOfTypeWhenInvokedTwiceThenTheSameInstanceIsReturned(): void
    {
        /** @When invoked twice for the same target type */
        $first = Descriptors::of(type: Amount::class);
        $second = Descriptors::of(type: Amount::class);

        /** @Then the same descriptor instance is returned */
        self::assertSame($first, $second);
    }

    public function testFindWhenMappingsArePresentAndTypeIsUnknownThenTheMissIsMemoized(): void
    {
        /** @Given a registry holding a mapping for the Tag type */
        $registry = new MappingRegistry(mappings: [Tag::class => Layout::from(paths: [])]);

        /** @And a reader for its memoized lookups */
        $resolved = new ReflectionProperty(class: MappingRegistry::class, property: 'resolved');

        /** @When a lookup misses an unrelated type */
        $found = $registry->find(type: Amount::class);

        /** @Then no mapping is returned */
        self::assertNull($found);

        /** @And the miss is memoized */
        self::assertNotEmpty($resolved->getValue($registry));
    }

    public function testLayoutTreeWhenResolvedTwiceForSameTypeAndNamingThenItIsMemoized(): void
    {
        /** @Given a layout codec without overrides */
        $codec = new LayoutCodec(paths: []);

        /** @And the naming strategy that drives the tree build */
        $naming = SnakeCase::create();

        /** @And a handle to its memoized tree resolution */
        $treeFor = new ReflectionMethod(objectOrMethod: LayoutCodec::class, method: 'treeFor');

        /** @When resolved twice for the same type and naming */
        $first = $treeFor->invoke($codec, Amount::class, $naming);
        $second = $treeFor->invoke($codec, Amount::class, $naming);

        /** @Then the same memoized layout tree instance is returned */
        self::assertSame($first, $second);
    }

    public function testFindWhenMappingsArePresentAndTypeIsCompatibleThenTheParentMappingIsReturned(): void
    {
        /** @Given a registry holding a mapping registered under the parent Tag type */
        $registry = new MappingRegistry(mappings: [Tag::class => Layout::from(paths: [])]);

        /** @When a lookup resolves a subclass of the registered type */
        $found = $registry->find(type: SpecializedTag::class);

        /** @Then a registered mapping bound to the parent type is returned */
        self::assertInstanceOf(RegisteredMapping::class, $found);

        /** @And it carries the registered parent type */
        self::assertSame(Tag::class, $found->registeredType);
    }
}
