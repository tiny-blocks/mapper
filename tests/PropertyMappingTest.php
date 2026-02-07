<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Customer;
use Test\TinyBlocks\Mapper\Models\Decimal;
use TinyBlocks\Mapper\KeyPreservation;

final class PropertyMappingTest extends TestCase
{
    public function testAllPropertiesMapped(): void
    {
        /** @Given a Customer with all properties */
        $customer = new Customer(name: 'John Doe', score: 100, gender: 'male');

        /** @When converting to array */
        $actual = $customer->toArray();

        /** @Then all properties should be mapped */
        self::assertSame([
            'name'   => 'John Doe',
            'score'  => 100,
            'gender' => 'male'
        ], $actual);
    }

    public function testNullPropertyIsPreserved(): void
    {
        /** @Given a Customer with null gender */
        $customer = new Customer(name: 'Jane Doe', score: 85, gender: null);

        /** @When converting to array */
        $actual = $customer->toArray();

        /** @Then null should be preserved */
        self::assertSame([
            'name'   => 'Jane Doe',
            'score'  => 85,
            'gender' => null
        ], $actual);
        self::assertNull($actual['gender']);
    }

    public function testDefaultValuesApplied(): void
    {
        /** @Given a Customer with only the required property */
        $customer = new Customer(name: 'Bob Smith');

        /** @When converting to array */
        $actual = $customer->toArray();

        /** @Then default values should be used */
        self::assertSame([
            'name'   => 'Bob Smith',
            'score'  => 0,
            'gender' => null
        ], $actual);
    }

    public function testFromIterableWithExplicitNull(): void
    {
        /** @Given data with an explicit null */
        $data = [
            'name'   => 'Alice',
            'score'  => 50,
            'gender' => null
        ];

        /** @When creating from iterable */
        $customer = Customer::fromIterable(iterable: $data);

        /** @Then null should be preserved */
        $actual = $customer->toArray();
        self::assertNull($actual['gender']);
        self::assertArrayHasKey('gender', $actual);
    }

    public function testFromIterableWithMissingOptionals(): void
    {
        /** @Given data without optional properties */
        $data = ['name' => 'Charlie'];

        /** @When creating from iterable */
        $customer = Customer::fromIterable(iterable: $data);

        /** @Then defaults should be applied */
        $actual = $customer->toArray();
        self::assertSame('Charlie', $actual['name']);
        self::assertSame(0, $actual['score']);
        self::assertNull($actual['gender']);
    }

    public function testCustomToArrayOverride(): void
    {
        /** @Given a Decimal with a custom toArray override */
        $decimal = new Decimal(value: 123.456);

        /** @When converting to array */
        $actual = $decimal->toArray();

        /** @Then the custom logic should be applied */
        self::assertSame(['value' => 123.456], $actual);
    }

    public function testFloatTypeIsPreserved(): void
    {
        /** @Given a Decimal with an integer-like float */
        $decimal = new Decimal(value: 100.00);

        /** @When converting to array */
        $actual = $decimal->toArray();

        /** @Then the float type should be preserved */
        self::assertIsFloat($actual['value']);
        self::assertSame(100.00, $actual['value']);
    }

    public function testCustomOverrideIgnoresKeyPreservation(): void
    {
        /** @Given a Decimal */
        $decimal = new Decimal(value: 999.99);

        /** @When converting with DISCARD */
        $withDiscard = $decimal->toArray(keyPreservation: KeyPreservation::DISCARD);

        /** @And converting with PRESERVE */
        $withPreserve = $decimal->toArray();

        /** @Then both should produce the same result due to custom override */
        self::assertSame($withDiscard, $withPreserve);
        self::assertSame(['value' => 999.99], $withDiscard);
    }
}
