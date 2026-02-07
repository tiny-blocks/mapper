<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper;

use ArrayIterator;
use DateTimeImmutable;
use DateTimeZone;
use Generator;
use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Configuration;
use Test\TinyBlocks\Mapper\Models\Currency;
use Test\TinyBlocks\Mapper\Models\Order;
use Test\TinyBlocks\Mapper\Models\Product;
use Test\TinyBlocks\Mapper\Models\Service;

final class IterableTypeMappingTest extends TestCase
{
    public function testArrayIteratorToArray(): void
    {
        /** @Given a Product with an ArrayIterator */
        $product = new Product(
            name: 'Laptop',
            amount: Amount::from(value: 1200.00, currency: Currency::USD),
            stockBatch: new ArrayIterator([1001, 1002, 1003])
        );

        /** @When converting to array */
        $actual = $product->toArray();

        /** @Then the ArrayIterator should be converted to array */
        self::assertSame('Laptop', $actual['name']);
        self::assertIsArray($actual['stockBatch']);
        self::assertSame([1001, 1002, 1003], $actual['stockBatch']);
    }

    public function testEmptyArrayIterator(): void
    {
        /** @Given a Product with an empty ArrayIterator */
        $product = new Product(
            name: 'Out of Stock',
            amount: Amount::from(value: 99.99, currency: Currency::BRL),
            stockBatch: new ArrayIterator([])
        );

        /** @When converting to array */
        $actual = $product->toArray();

        /** @Then stockBatch should be an empty array */
        self::assertSame([], $actual['stockBatch']);
    }

    public function testArrayIteratorWithAssociativeKeys(): void
    {
        /** @Given an ArrayIterator with associative data */
        $product = new Product(
            name: 'Phone',
            amount: Amount::from(value: 800.00, currency: Currency::USD),
            stockBatch: new ArrayIterator([
                'batch1' => 100,
                'batch2' => 200,
                'batch3' => 300
            ])
        );

        /** @When converting to array */
        $actual = $product->toArray();

        /** @Then associative keys should be preserved */
        self::assertSame([
            'batch1' => 100,
            'batch2' => 200,
            'batch3' => 300
        ], $actual['stockBatch']);
    }

    public function testArrayIteratorFromIterable(): void
    {
        /** @Given product data with an array for stockBatch */
        $data = [
            'name'       => 'Tablet',
            'amount'     => ['value' => 500.00, 'currency' => 'USD'],
            'stockBatch' => [5001, 5002, 5003]
        ];

        /** @When creating from iterable */
        $product = Product::fromIterable(iterable: $data);

        /** @Then the ArrayIterator should be created from the array */
        $actual = $product->toArray();
        self::assertSame([5001, 5002, 5003], $actual['stockBatch']);
    }

    public function testGeneratorToArray(): void
    {
        /** @Given an Order with a Generator for items */
        $order = new Order(
            id: 'order-123',
            items: $this->createItemsGenerator(),
            createdAt: new DateTimeImmutable('2025-01-01 10:00:00', new DateTimeZone('UTC'))
        );

        /** @When converting to array */
        $actual = $order->toArray();

        /** @Then the Generator should be converted to array */
        self::assertSame('order-123', $actual['id']);
        self::assertIsArray($actual['items']);
        self::assertCount(3, $actual['items']);
        self::assertSame('2025-01-01T10:00:00+00:00', $actual['createdAt']);
    }

    public function testMultipleGenerators(): void
    {
        /** @Given a Configuration with multiple Generators */
        $config = new Configuration(
            id: $this->createIdGenerator(),
            options: $this->createOptionsGenerator()
        );

        /** @When converting to array */
        $actual = $config->toArray();

        /** @Then both Generators should be converted to arrays */
        self::assertIsArray($actual['id']);
        self::assertIsArray($actual['options']);
        self::assertSame(['uuid-1', 'uuid-2', 'uuid-3'], $actual['id']);
        self::assertSame(['debug' => true, 'timeout' => 30], $actual['options']);
    }

    public function testEmptyGenerator(): void
    {
        /** @Given an Order with an empty Generator */
        $order = new Order(
            id: 'empty-order',
            items: $this->createEmptyGenerator(),
            createdAt: new DateTimeImmutable('2025-01-01')
        );

        /** @When converting to array */
        $actual = $order->toArray();

        /** @Then items should be an empty array */
        self::assertSame([], $actual['items']);
    }

    public function testGeneratorFromIterable(): void
    {
        /** @Given order data with an items array */
        $data = [
            'id'        => 'order-789',
            'items'     => [
                ['sku' => 'A1', 'quantity' => 5],
                ['sku' => 'B2', 'quantity' => 3]
            ],
            'createdAt' => '2025-02-01T15:30:00+00:00'
        ];

        /** @When creating from iterable */
        $order = Order::fromIterable(iterable: $data);

        /** @Then the order should be created with a Generator */
        $actual = $order->toArray();

        self::assertSame('order-789', $actual['id']);
        self::assertIsArray($actual['items']);
        self::assertCount(2, $actual['items']);
    }

    public function testGeneratorFromIterableWithDateTimeInstance(): void
    {
        /** @Given order data with a DateTimeImmutable instance */
        $data = [
            'id'        => 'order-999',
            'items'     => [['sku' => 'C3', 'quantity' => 1]],
            'createdAt' => new DateTimeImmutable('2025-06-15T12:00:00+00:00')
        ];

        /** @When creating from iterable */
        $order = Order::fromIterable(iterable: $data);

        /** @Then the DateTimeImmutable should be preserved */
        $actual = $order->toArray();

        self::assertSame('order-999', $actual['id']);
        self::assertSame('2025-06-15T12:00:00+00:00', $actual['createdAt']);
    }

    public function testGeneratorFromIterableWithScalarItems(): void
    {
        /** @Given order data with a single scalar value for items */
        $data = [
            'id'        => 'order-scalar',
            'items'     => 'single-item',
            'createdAt' => '2025-03-01T00:00:00+00:00'
        ];

        /** @When creating from iterable */
        $order = Order::fromIterable(iterable: $data);

        /** @Then the scalar should be yielded as a single-element array */
        $actual = $order->toArray();

        self::assertSame('order-scalar', $actual['id']);
        self::assertSame(['single-item'], $actual['items']);
    }

    public function testClosureToArray(): void
    {
        /** @Given a Service with a Closure */
        $service = new Service(action: static fn() => 'executed');

        /** @When converting to array */
        $actual = $service->toArray();

        /** @Then the Closure should be serialized as an empty array */
        self::assertSame(['action' => []], $actual);
    }

    public function testClosureWithCapturedVariables(): void
    {
        /** @Given a Service with a Closure capturing variables */
        $multiplier = 5;
        $service = new Service(action: static fn($x) => $x * $multiplier);

        /** @When converting to array */
        $actual = $service->toArray();

        /** @Then the Closure should be serialized as an empty array */
        self::assertSame(['action' => []], $actual);
    }

    public function testClosureFromIterable(): void
    {
        /** @Given data with a Closure */
        $data = ['action' => fn() => 'test'];

        /** @When creating from iterable */
        $service = Service::fromIterable(iterable: $data);

        /** @Then the Service should be created */
        $actual = $service->toArray();
        self::assertArrayHasKey('action', $actual);
    }

    public function testClosureProducesEmptyArrayNotNull(): void
    {
        /** @Given a Service with a no-op Closure */
        $service = new Service(action: static fn() => null);

        /** @When converting to array */
        $actual = $service->toArray();

        /** @Then action should be an empty array, not null */
        self::assertIsArray($actual['action']);
        self::assertEmpty($actual['action']);
    }

    private function createItemsGenerator(): Generator
    {
        yield ['sku' => 'ITEM-001', 'quantity' => 2];
        yield ['sku' => 'ITEM-002', 'quantity' => 1];
        yield ['sku' => 'ITEM-003', 'quantity' => 5];
    }

    private function createIdGenerator(): Generator
    {
        yield 'uuid-1';
        yield 'uuid-2';
        yield 'uuid-3';
    }

    private function createOptionsGenerator(): Generator
    {
        yield 'debug' => true;
        yield 'timeout' => 30;
    }

    private function createEmptyGenerator(): Generator
    {
        yield from [];
    }
}
