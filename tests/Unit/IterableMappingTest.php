<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Currency;
use Test\TinyBlocks\Mapper\Models\Notes;
use Test\TinyBlocks\Mapper\Models\Refund;
use Test\TinyBlocks\Mapper\Models\Refunds;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Mapper\Serializable;

final class IterableMappingTest extends TestCase
{
    public function testToObjectWhenNoElementTypeThenSourceElementsArePassedThrough(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a list of scalars is mapped into a collection with no declared element type */
        $notes = $mapper->toObject(type: Notes::class, source: ['first note', 'second note']);

        /** @Then the elements are passed through unchanged */
        self::assertSame(['first note', 'second note'], $notes->toArray());
    }

    public function testToJsonWhenCollectionTypedAsSerializableThenRespondsWithoutArguments(): void
    {
        /** @Given a collection referenced only through the Serializable contract */
        $serializable = Refunds::createFrom(elements: [
            new Refund(amount: new Amount(amount: 100, currency: Currency::BRL), reference: 'r-1')
        ]);

        /** @When it is serialized to JSON through the parameterless contract */
        $json = (static fn(Serializable $value): string => $value->toJson())($serializable);

        /** @Then the JSON carries the serialized elements */
        self::assertSame('[{"amount":{"amount":100,"currency":"BRL"},"reference":"r-1"}]', $json);
    }

    public function testToObjectAndToArrayWhenElementTypedCollectionThenRoundTripIsLossless(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @And an element-typed collection of refunds */
        $original = Refunds::createFrom(elements: [
            new Refund(amount: new Amount(amount: 100, currency: Currency::BRL), reference: 'r-1'),
            new Refund(amount: new Amount(amount: 200, currency: Currency::BRL), reference: 'r-2')
        ]);

        /** @When the collection is serialized to rows and rebuilt */
        $rebuilt = $mapper->toObject(type: Refunds::class, source: $mapper->toArray(source: $original));

        /** @Then the rebuilt collection equals the original */
        self::assertEquals($original, $rebuilt);
    }
}
