<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Box;
use Test\TinyBlocks\Mapper\Models\Carton;
use Test\TinyBlocks\Mapper\Models\Coupon;
use Test\TinyBlocks\Mapper\Models\Crate;
use Test\TinyBlocks\Mapper\Models\Currency;
use Test\TinyBlocks\Mapper\Models\Moment;
use Test\TinyBlocks\Mapper\Models\Mood;
use Test\TinyBlocks\Mapper\Models\Order;
use Test\TinyBlocks\Mapper\Models\OrderStatus;
use Test\TinyBlocks\Mapper\Models\Pulse;
use Test\TinyBlocks\Mapper\Models\Range;
use Test\TinyBlocks\Mapper\Models\Receipt;
use Test\TinyBlocks\Mapper\Models\Reel;
use Test\TinyBlocks\Mapper\Models\Reference;
use Test\TinyBlocks\Mapper\Models\Seal;
use Test\TinyBlocks\Mapper\Models\Severity;
use Test\TinyBlocks\Mapper\Models\Sku;
use Test\TinyBlocks\Mapper\Models\Ticket;
use Test\TinyBlocks\Mapper\Models\Token;
use TinyBlocks\Mapper\Codec;
use TinyBlocks\Mapper\Mapper;

final class DelegationTest extends TestCase
{
    public function testToArrayWhenTwoOrMorePropertiesThenObjectReflects(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a two-property value object is serialized */
        $array = $mapper->toArray(source: Range::of(lowerBound: 1, upperBound: 5));

        /** @Then both properties are kept under their own keys */
        self::assertSame(['lowerBound' => 1, 'upperBound' => 5], $array);
    }

    public function testToArrayWhenWrapperAroundTraversableThenItReflects(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a single-property wrapper around a traversable is serialized */
        $array = $mapper->toArray(source: new Carton(reel: new Reel(frames: ['a', 'b'])));

        /** @Then the traversable stays a nested array under its key */
        self::assertSame(['reel' => ['a', 'b']], $array);
    }

    public function testToArrayWhenWrapperAroundUnionPropertyThenItReflects(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a single-property wrapper around a union-typed value object is serialized */
        $array = $mapper->toArray(source: new Box(token: new Token(code: 'x')));

        /** @Then the union-typed inner stays under its key */
        self::assertSame(['token' => 'x'], $array);
    }

    public function testToArrayWhenWrapperAroundMultiFieldObjectThenItReflects(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a single-property wrapper around a multi-field object is serialized */
        $array = $mapper->toArray(source: new Order(amount: new Amount(amount: 5000, currency: Currency::BRL)));

        /** @Then the multi-field object stays nested under the property key */
        self::assertSame(['amount' => ['amount' => 5000, 'currency' => 'BRL']], $array);
    }

    public function testToArrayWhenWrapperAroundPureEnumThenInnerNameIsEmitted(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a single-property wrapper around a pure enum is serialized */
        $array = $mapper->toArray(source: new Mood(severity: Severity::HIGH));

        /** @Then the wrapper unwraps to the inner case name */
        self::assertSame(['HIGH'], $array);
    }

    public function testToArrayWhenScalarCodecOnWrapperThenItOverridesDelegation(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a wrapper carrying its own ScalarCodec is serialized */
        $array = $mapper->toArray(source: Seal::fromToken(token: 'abc'));

        /** @Then the wrapper's own encode wins over delegation to the inner type */
        self::assertSame(['seal:ABC'], $array);
    }

    public function testToArrayWhenWrapperAroundDateTimeThenInnerStringIsEmitted(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a single-property wrapper around a date-time is serialized */
        $array = $mapper->toArray(source: new Moment(at: new DateTimeImmutable('2024-01-02T03:04:05+00:00')));

        /** @Then the wrapper unwraps to the inner date-time string */
        self::assertSame(['2024-01-02T03:04:05+00:00'], $array);
    }

    public function testToArrayWhenWrapperAroundBackedEnumThenInnerValueIsEmitted(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a single-property wrapper around a backed enum is serialized */
        $array = $mapper->toArray(source: new Pulse(status: OrderStatus::PAID));

        /** @Then the wrapper unwraps to the inner backed value */
        self::assertSame(['paid'], $array);
    }

    public function testToArrayWhenNestedWrapperFunnelsToScalarThenScalarIsEmitted(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a wrapper around a single-scalar value object is serialized */
        $array = $mapper->toArray(source: new Crate(sku: Sku::from(value: 'abc')));

        /** @Then the nested wrappers funnel down to the inner scalar */
        self::assertSame(['ABC'], $array);
    }

    public function testToArrayWhenWrapperAroundScalarCodecThenInnerScalarIsEmitted(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a single-property wrapper around a ScalarCodec type is serialized */
        $array = $mapper->toArray(source: new Ticket(reference: Reference::fromText(value: 'alpha')));

        /** @Then the wrapper unwraps to the inner scalar form */
        self::assertSame(['alpha'], $array);
    }

    public function testToArrayWhenRegisteredCodecOnWrapperThenItOverridesDelegation(): void
    {
        /** @Given a mapper with a Codec registered for the wrapper type */
        $mapper = Mapper::create()->withMapping(
            type: Pulse::class,
            mapping: Codec::from(
                decode: static fn(string $raw): Pulse => new Pulse(status: OrderStatus::from($raw)),
                encode: static fn(Pulse $pulse): string => 'registered'
            )
        );

        /** @When the wrapper is serialized */
        $array = $mapper->toArray(source: new Pulse(status: OrderStatus::PAID));

        /** @Then the registered encode wins over delegation to the inner type */
        self::assertSame(['registered'], $array);
    }

    public function testToObjectWhenScalarCodecOnWrapperThenDecodeOverridesDelegation(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a scalar source is mapped to a wrapper carrying its own ScalarCodec */
        $seal = $mapper->toObject(type: Seal::class, source: 'xyz');

        /** @Then the wrapper's own decode wins over delegation to the inner type */
        self::assertEquals(Seal::fromToken(token: 'xyz'), $seal);
    }

    public function testToArrayWhenNestedWrapperFunnelsToScalarCodecThenScalarIsEmitted(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a wrapper around a wrapper around a ScalarCodec type is serialized */
        $array = $mapper->toArray(
            source: new Coupon(ticket: new Ticket(reference: Reference::fromText(value: 'beta')))
        );

        /** @Then the nested wrappers funnel down to the inner scalar form */
        self::assertSame(['beta'], $array);
    }

    public function testToObjectAndToArrayWhenWrappersAreNestedThenRoundTripReconstructsFromScalars(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @And an aggregate of single-property wrappers over reducible inner types */
        $original = new Receipt(
            mood: new Mood(severity: Severity::HIGH),
            crate: new Crate(sku: Sku::from(value: 'abc')),
            pulse: new Pulse(status: OrderStatus::PAID),
            coupon: new Coupon(ticket: new Ticket(reference: Reference::fromText(value: 'beta'))),
            moment: new Moment(at: new DateTimeImmutable('2024-01-02T03:04:05+00:00')),
            ticket: new Ticket(reference: Reference::fromText(value: 'alpha'))
        );

        /** @When the aggregate is serialized to scalars and rebuilt */
        $rebuilt = $mapper->toObject(type: Receipt::class, source: $mapper->toArray(source: $original));

        /** @Then the rebuilt aggregate equals the original */
        self::assertEquals($original, $rebuilt);
    }
}
