<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Currency;
use Test\TinyBlocks\Mapper\Models\LineItem;
use Test\TinyBlocks\Mapper\Models\Range;
use Test\TinyBlocks\Mapper\Models\Shipment;
use Test\TinyBlocks\Mapper\Models\Sku;
use Test\TinyBlocks\Mapper\Models\Version;
use Test\TinyBlocks\Mapper\Models\Weekday;
use TinyBlocks\Mapper\Exceptions\UnmappableSource;
use TinyBlocks\Mapper\FactoryMethod;
use TinyBlocks\Mapper\Layout;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Mapper\SnakeCase;

final class FactoryMappingTest extends TestCase
{
    public function testToObjectWhenLookupFactoryThenNameMapsToCanonicalCase(): void
    {
        /** @Given a mapper that builds Weekday through its lookup factory */
        $mapper = Mapper::create()
            ->withMapping(type: Weekday::class, mapping: FactoryMethod::using(method: 'fromName'));

        /** @When a short day name is mapped to a Weekday */
        $weekday = $mapper->toObject(type: Weekday::class, source: 'mon');

        /** @Then the factory resolves it to the canonical case name */
        self::assertSame('monday', $weekday->name());
    }

    public function testToObjectWhenLayoutFactoryThenGraphIsBuiltThroughFactory(): void
    {
        /** @Given a mapper that builds LineItem from a flat row through its factory */
        $mapper = Mapper::create()
            ->withNaming(namingStrategy: SnakeCase::create())
            ->withMapping(
                type: LineItem::class,
                mapping: Layout::from(paths: ['amount.amount' => 'line_amount_value'], factory: 'of')
            );

        /** @When a flat row is hydrated through the Layout and its factory */
        $lineItem = $mapper->toObject(type: LineItem::class, source: [
            'line_amount_value' => 1000,
            'amount_currency'   => 'BRL',
            'reference'         => 'r-1'
        ]);

        /** @Then the reference was normalized by the factory the Layout delegated to */
        self::assertSame('R-1', $lineItem->reference());
    }

    public function testToObjectWhenParseFactoryThenStringParsesToCanonicalForm(): void
    {
        /** @Given a mapper that builds Version through its parsing factory */
        $mapper = Mapper::create()
            ->withMapping(type: Version::class, mapping: FactoryMethod::using(method: 'fromString'));

        /** @When a version string with extra segments is parsed */
        $version = $mapper->toObject(type: Version::class, source: '1.2.3');

        /** @Then the factory parses it into its canonical two-segment form */
        self::assertSame('1.2', $version->value());
    }

    public function testToObjectWhenScalarNestedFactoryTypeThenRegistryIsHonored(): void
    {
        /** @Given a mapper that builds the nested Sku through its factory */
        $mapper = Mapper::create()->withMapping(type: Sku::class, mapping: FactoryMethod::using(method: 'from'));

        /** @When a parent is hydrated with a scalar value for its Sku property */
        $shipment = $mapper->toObject(type: Shipment::class, source: ['sku' => 'abc', 'carrier' => 'dhl']);

        /** @Then the scalar was routed through the registered factory, not the single-property unwrap */
        self::assertSame('ABC', $shipment->sku->value());
    }

    public function testToObjectAndToArrayWhenLayoutFactoryThenRoundTripIsLossless(): void
    {
        /** @Given a mapper that builds LineItem from a flat row through its factory */
        $mapper = Mapper::create()
            ->withNaming(namingStrategy: SnakeCase::create())
            ->withMapping(
                type: LineItem::class,
                mapping: Layout::from(paths: ['amount.amount' => 'line_amount_value'], factory: 'of')
            );

        /** @And an original line item */
        $original = LineItem::of(amount: new Amount(amount: 1000, currency: Currency::BRL), reference: 'r-1');

        /** @When the line item is serialized to a flat row and rebuilt */
        $rebuilt = $mapper->toObject(type: LineItem::class, source: $mapper->toArray(source: $original));

        /** @Then the rebuilt line item equals the original */
        self::assertEquals($original, $rebuilt);
    }

    public function testToObjectAndToArrayWhenCompoundFactoryThenRoundTripIsLossless(): void
    {
        /** @Given a mapper that builds Range through its multi-argument factory */
        $mapper = Mapper::create()
            ->withNaming(namingStrategy: SnakeCase::create())
            ->withMapping(type: Range::class, mapping: FactoryMethod::using(method: 'of'));

        /** @And an original range */
        $original = Range::of(lowerBound: 1, upperBound: 10);

        /** @When the range is serialized to an array and rebuilt */
        $rebuilt = $mapper->toObject(type: Range::class, source: $mapper->toArray(source: $original));

        /** @Then the rebuilt range equals the original */
        self::assertEquals($original, $rebuilt);
    }

    public function testToObjectWhenCompoundFactoryReordersBoundsThenInvariantIsEnforced(): void
    {
        /** @Given a mapper that builds Range through its multi-argument factory */
        $mapper = Mapper::create()
            ->withNaming(namingStrategy: SnakeCase::create())
            ->withMapping(type: Range::class, mapping: FactoryMethod::using(method: 'of'));

        /** @When a row carrying the bounds in reverse order is hydrated */
        $range = $mapper->toObject(type: Range::class, source: ['lower_bound' => 10, 'upper_bound' => 1]);

        /** @Then the factory enforced the ordering invariant that reflection injection would skip */
        self::assertSame([1, 10], [$range->lowerBound(), $range->upperBound()]);
    }

    public function testToObjectAndToArrayWhenScalarNestedFactoryTypeThenRoundTripIsLossless(): void
    {
        /** @Given a mapper that builds the nested Sku through its factory */
        $mapper = Mapper::create()->withMapping(type: Sku::class, mapping: FactoryMethod::using(method: 'from'));

        /** @And an original shipment carrying a factory-built Sku */
        $original = new Shipment(sku: Sku::from(value: 'abc'), carrier: 'dhl');

        /** @When the shipment is serialized to an array and rebuilt */
        $rebuilt = $mapper->toObject(type: Shipment::class, source: $mapper->toArray(source: $original));

        /** @Then the rebuilt shipment equals the original */
        self::assertEquals($original, $rebuilt);
    }

    public function testToObjectWhenMultiArgFactorySourceMissesKeyThenUnmappableSourceIsRaised(): void
    {
        /** @Given a mapper that builds Range through its multi-argument factory */
        $mapper = Mapper::create()
            ->withNaming(namingStrategy: SnakeCase::create())
            ->withMapping(type: Range::class, mapping: FactoryMethod::using(method: 'of'));

        /** @Then an unmappable-source exception is raised */
        $this->expectException(UnmappableSource::class);

        /** @When a row missing one factory parameter key is hydrated */
        $mapper->toObject(type: Range::class, source: ['lower_bound' => 1]);
    }

    public function testToObjectWhenMultiArgFactorySourceIsJsonStringThenUnmappableSourceIsRaised(): void
    {
        /** @Given a mapper that builds Range through its multi-argument factory */
        $mapper = Mapper::create()
            ->withNaming(namingStrategy: SnakeCase::create())
            ->withMapping(type: Range::class, mapping: FactoryMethod::using(method: 'of'));

        /** @Then an unmappable-source exception is raised */
        $this->expectException(UnmappableSource::class);

        /** @When a multi-parameter factory is given a top-level JSON string source */
        $mapper->toObject(type: Range::class, source: '{"lower_bound":1,"upper_bound":10}');
    }
}
