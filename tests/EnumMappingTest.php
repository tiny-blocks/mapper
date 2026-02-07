<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Currency;
use Test\TinyBlocks\Mapper\Models\Dragon;
use Test\TinyBlocks\Mapper\Models\DragonSkills;
use Test\TinyBlocks\Mapper\Models\DragonType;
use Test\TinyBlocks\Mapper\Models\ShippingAddress;
use Test\TinyBlocks\Mapper\Models\ShippingCountry;
use Test\TinyBlocks\Mapper\Models\ShippingState;
use TinyBlocks\Mapper\Internal\Exceptions\InvalidCast;

final class EnumMappingTest extends TestCase
{
    public function testBackedStringEnum(): void
    {
        /** @Given an Amount with a backed string enum */
        $amount = Amount::from(value: 100.00, currency: Currency::BRL);

        /** @When converting to array */
        $actual = $amount->toArray();

        /** @Then the enum value should be used */
        self::assertSame('BRL', $actual['currency']);
        self::assertIsString($actual['currency']);
    }

    public function testPureEnum(): void
    {
        /** @Given a Dragon with a pure enum */
        $dragon = new Dragon(
            name: 'Smaug',
            type: DragonType::FIRE,
            power: 9999.99,
            skills: []
        );

        /** @When converting to array */
        $actual = $dragon->toArray();

        /** @Then the enum name should be used */
        self::assertSame('FIRE', $actual['type']);
    }

    public function testBackedStringEnumArray(): void
    {
        /** @Given a Dragon with an array of backed enums */
        $dragon = new Dragon(
            name: 'Alduin',
            type: DragonType::FIRE,
            power: 10000.00,
            skills: [
                DragonSkills::FLY,
                DragonSkills::ELEMENTAL_BREATH,
                DragonSkills::REGENERATION
            ]
        );

        /** @When converting to array */
        $actual = $dragon->toArray();

        /** @Then enum values should appear in the array */
        self::assertSame([
            'fly',
            'elemental_breath',
            'regeneration'
        ], $actual['skills']);
    }

    public function testMixedEnumTypes(): void
    {
        /** @Given a ShippingAddress with both pure and backed enums */
        $address = new ShippingAddress(
            city: 'São Paulo',
            state: ShippingState::SP,
            street: 'Av Paulista',
            number: 1000,
            country: ShippingCountry::BRAZIL
        );

        /** @When converting to array */
        $actual = $address->toArray();

        /** @Then both enum types should be handled correctly */
        self::assertSame('SP', $actual['state']);
        self::assertSame('BR', $actual['country']);
    }

    public function testBackedEnumFromIterable(): void
    {
        /** @Given data with a backed enum value */
        $data = [
            'value'    => 500.00,
            'currency' => 'USD'
        ];

        /** @When creating from iterable */
        $amount = Amount::fromIterable(iterable: $data);

        /** @Then the enum should be reconstructed */
        $actual = $amount->toArray();
        self::assertSame('USD', $actual['currency']);
    }

    public function testPureEnumFromIterable(): void
    {
        /** @Given data with a pure enum name */
        $data = [
            'city'    => 'New York',
            'state'   => 'NY',
            'street'  => 'Broadway',
            'number'  => 100,
            'country' => 'US'
        ];

        /** @When creating from iterable */
        $address = ShippingAddress::fromIterable(iterable: $data);

        /** @Then both enums should be reconstructed */
        $actual = $address->toArray();
        self::assertSame('NY', $actual['state']);
        self::assertSame('US', $actual['country']);
    }

    public function testEnumArrayFromIterable(): void
    {
        /** @Given Dragon data with skill values */
        $data = [
            'name'   => 'Bahamut',
            'type'   => 'FIRE',
            'power'  => 15000.0,
            'skills' => ['fly', 'spell', 'elemental_breath']
        ];

        /** @When creating from iterable */
        $dragon = Dragon::fromIterable(iterable: $data);

        /** @Then enums should be reconstructed */
        $actual = $dragon->toArray();
        self::assertSame('FIRE', $actual['type']);
        self::assertSame(['fly', 'spell', 'elemental_breath'], $actual['skills']);
    }

    public function testInvalidEnumValueThrowsException(): void
    {
        /** @Given data with an invalid currency value */
        $data = [
            'value'    => 250.00,
            'currency' => 'INVALID'
        ];

        /** @Then an exception should be thrown */
        $this->expectException(InvalidCast::class);
        $this->expectExceptionMessage('Invalid value <INVALID> for enum <Test\TinyBlocks\Mapper\Models\Currency>.');

        /** @When creating from iterable */
        Amount::fromIterable(iterable: $data);
    }
}
