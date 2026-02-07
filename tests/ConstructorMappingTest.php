<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Collection;
use Test\TinyBlocks\Mapper\Models\Currency;
use Test\TinyBlocks\Mapper\Models\Webhook;

final class ConstructorMappingTest extends TestCase
{
    public function testPrivateConstructorToArray(): void
    {
        /** @Given an Amount created via factory with a private constructor */
        $amount = Amount::from(value: 1500.00, currency: Currency::USD);

        /** @When converting to array */
        $actual = $amount->toArray();

        /** @Then the amount should be mapped correctly */
        self::assertSame([
            'value'    => 1500.00,
            'currency' => 'USD'
        ], $actual);
    }

    public function testPrivateConstructorFromIterable(): void
    {
        /** @Given array data for Amount with a raw enum value */
        $data = [
            'value'    => 2500.50,
            'currency' => 'BRL'
        ];

        /** @When creating from iterable */
        $amount = Amount::fromIterable(iterable: $data);

        /** @Then the Amount should be created successfully */
        $actual = $amount->toArray();
        self::assertSame(2500.50, $actual['value']);
        self::assertSame('BRL', $actual['currency']);
    }

    public function testPrivateConstructorFromIterableWithDifferentEnum(): void
    {
        /** @Given array data with USD currency */
        $data = [
            'value'    => 999.99,
            'currency' => 'USD'
        ];

        /** @When creating from iterable */
        $amount = Amount::fromIterable(iterable: $data);

        /** @Then the enum should be reconstructed correctly */
        $actual = $amount->toArray();
        self::assertSame('USD', $actual['currency']);
    }

    public function testPrivateConstructorCollectionToArray(): void
    {
        /** @Given a Collection created via factory with a private constructor */
        $collection = Collection::createFrom(elements: ['a', 'b', 'c']);

        /** @When converting to array */
        $actual = $collection->toArray();

        /** @Then elements should be preserved */
        self::assertSame(['a', 'b', 'c'], $actual);
        self::assertSame(3, $collection->count());
    }

    public function testPrivateConstructorCollectionWithComplexObjects(): void
    {
        /** @Given a Collection with Amount objects */
        $collection = Collection::createFrom(elements: [
            Amount::from(value: 100.00, currency: Currency::USD),
            Amount::from(value: 200.00, currency: Currency::BRL)
        ]);

        /** @When converting to array */
        $actual = $collection->toArray();

        /** @Then amounts should be converted */
        self::assertCount(2, $actual);
        self::assertSame([
            ['value' => 100.00, 'currency' => 'USD'],
            ['value' => 200.00, 'currency' => 'BRL']
        ], $actual);
    }

    public function testNoConstructorToArray(): void
    {
        /** @Given a Webhook with no constructor and assigned properties */
        $webhook = new Webhook();
        $webhook->url = 'https://example.com/hook';
        $webhook->active = true;

        /** @When converting to array */
        $actual = $webhook->toArray();

        /** @Then properties should be mapped correctly */
        self::assertSame([
            'url'    => 'https://example.com/hook',
            'active' => true
        ], $actual);
    }

    public function testNoConstructorWithDefaults(): void
    {
        /** @Given a Webhook with no constructor using default values */
        $webhook = new Webhook();

        /** @When converting to array */
        $actual = $webhook->toArray();

        /** @Then default values should be mapped */
        self::assertSame([
            'url'    => '',
            'active' => false
        ], $actual);
    }

    public function testNoConstructorFromIterable(): void
    {
        /** @Given data for a Webhook with no constructor */
        $data = [
            'url'    => 'https://api.example.com/webhook',
            'active' => true
        ];

        /** @When creating from iterable */
        $webhook = Webhook::fromIterable(iterable: $data);

        /** @Then the Webhook should be created with default values */
        self::assertInstanceOf(Webhook::class, $webhook);
    }

    public function testNoConstructorDefaultsToJson(): void
    {
        /** @Given a Webhook with all default (empty) values */
        $webhook = new Webhook();

        /** @When converting to JSON */
        $actual = $webhook->toJson();

        /** @Then should produce JSON with default values */
        self::assertSame('{"url":"","active":false}', $actual);
    }
}
