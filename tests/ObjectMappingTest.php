<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper;

use ArrayIterator;
use DateTimeImmutable;
use DateTimeZone;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Catalog;
use Test\TinyBlocks\Mapper\Models\Configuration;
use Test\TinyBlocks\Mapper\Models\Currency;
use Test\TinyBlocks\Mapper\Models\Description;
use Test\TinyBlocks\Mapper\Models\Employee;
use Test\TinyBlocks\Mapper\Models\Inventory;
use Test\TinyBlocks\Mapper\Models\Member;
use Test\TinyBlocks\Mapper\Models\MemberId;
use Test\TinyBlocks\Mapper\Models\Members;
use Test\TinyBlocks\Mapper\Models\Order;
use Test\TinyBlocks\Mapper\Models\Organization;
use Test\TinyBlocks\Mapper\Models\OrganizationId;
use Test\TinyBlocks\Mapper\Models\Product;
use Test\TinyBlocks\Mapper\Models\ProductStatus;
use Test\TinyBlocks\Mapper\Models\Service;
use Test\TinyBlocks\Mapper\Models\Tag;
use Test\TinyBlocks\Mapper\Models\Uuid;
use Test\TinyBlocks\Mapper\Models\Webhook;
use TinyBlocks\Mapper\KeyPreservation;
use TinyBlocks\Mapper\ObjectMapper;

final class ObjectMappingTest extends TestCase
{
    #[DataProvider('objectProvider')]
    public function testObject(string $class, iterable $iterable, array $expected): void
    {
        /** @Given an Object */
        /** @var ObjectMapper $class */
        $object = $class::fromIterable(iterable: $iterable);

        /** @When mapping the object to an array */
        $actual = $object->toArray();

        /** @Then the mapped array should have expected values */
        self::assertSame($expected, $actual);

        /** @And when mapping the object to JSON */
        $actual = $object->toJson();

        /** @Then the mapped JSON should have expected values */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $actual);
    }

    public function testObjectWithGenerator(): void
    {
        /** @Given an Order with a Generator of items */
        $order = new Order(
            id: new Uuid(value: '123e4567-e89b-12d3-a456-426614174000'),
            items: (function (): Generator {
                yield ['sku' => 'ITEM-001', 'quantity' => 2];
                yield ['sku' => 'ITEM-002', 'quantity' => 1];
                yield ['sku' => 'ITEM-003', 'quantity' => 5];
            })(),
            createdAt: new DateTimeImmutable('2025-01-01 10:00:00', new DateTimeZone('UTC'))
        );

        /** @When mapping the Order to an array */
        $actual = $order->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [
            'id'        => '123e4567-e89b-12d3-a456-426614174000',
            'items'     => [
                ['sku' => 'ITEM-001', 'quantity' => 2],
                ['sku' => 'ITEM-002', 'quantity' => 1],
                ['sku' => 'ITEM-003', 'quantity' => 5]
            ],
            'createdAt' => '2025-01-01T10:00:00+00:00'
        ];

        self::assertSame($expected, $actual);

        /** @And when mapping the Order to JSON */
        $order = Order::fromIterable(iterable: $expected);
        $actual = $order->toJson();

        /** @Then the mapped JSON should have expected values */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $actual);
    }

    #[DataProvider('objectDiscardKeysProvider')]
    public function testObjectDiscardKeys(ObjectMapper $object, array $expected): void
    {
        /** @Given an Object */
        /** @When mapping the object to an array with discard key preservation */
        $actual = $object->toArray(keyPreservation: KeyPreservation::DISCARD);

        /** @Then the mapped array should have expected values */
        self::assertSame($expected, $actual);

        /** @And when mapping the object to JSON with discard key preservation */
        $actual = $object->toJson(keyPreservation: KeyPreservation::DISCARD);

        /** @Then the mapped JSON should have expected values */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $actual);
    }

    public function testObjectWithoutConstructor(): void
    {
        /** @Given a Webhook with no constructor */
        $webhook = new Webhook();
        $webhook->url = 'https://example.com/hook';
        $webhook->active = true;

        /** @When mapping the Webhook to an array */
        $actual = $webhook->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [
            'url'    => 'https://example.com/hook',
            'active' => true
        ];

        self::assertSame($expected, $actual);

        /** @And when mapping the Webhook to JSON */
        $actual = $webhook->toJson();

        /** @Then the mapped JSON should have expected values */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $actual);
    }

    public function testObjectWithStaticProperties(): void
    {
        /** @Given a Webhook with no constructor and static properties */
        $webhook = new Webhook();
        $webhook->url = 'https://example.com/static-test';
        $webhook->active = true;
        Webhook::$timeout = 60;

        /** @When mapping the Webhook to an array */
        $actual = $webhook->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [
            'url'    => 'https://example.com/static-test',
            'active' => true
        ];

        self::assertSame($expected, $actual);

        /** @And when mapping the Webhook to JSON */
        $actual = $webhook->toJson();

        /** @Then the mapped JSON should have expected values */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $actual);
    }

    public function testObjectWithPrivateConstructor(): void
    {
        /** @Given an Amount object with a private constructor */
        $amount = Amount::fromIterable(iterable: ['value' => 150.75, 'currency' => 'BRL']);

        /** @When mapping the Amount to an array */
        $actual = $amount->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [
            'value'    => 150.75,
            'currency' => 'BRL'
        ];

        self::assertSame($expected, $actual);

        /** @And when mapping the Amount to JSON */
        $actual = $amount->toJson();

        /** @Then the mapped JSON should have expected values */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $actual);
    }

    public function testObjectWithNonIterableGeneratorValue(): void
    {
        /** @Given a Configuration object with a non-iterable Generator value */
        $configuration = Configuration::fromIterable(iterable: ['id' => 42, 'options' => 'single-option']);

        /** @When mapping the Configuration to an array */
        $actual = $configuration->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [
            'id'      => [42,],
            'options' => ['single-option'],
        ];

        self::assertSame($expected, $actual);
    }

    public function testObjectWithNonTraversableProperty(): void
    {
        /** @Given a Catalog object with a non-traversable property */
        $catalog = new Catalog(name: 'Electronics', items: ['laptop', 'phone', 'tablet']);

        /** @When mapping the Catalog to an array */
        $actual = $catalog->toArray();

        /** @Then the mapped array should have expected values */
        $expected = ['laptop', 'phone', 'tablet'];

        self::assertSame($expected, $actual);

        /** @And when mapping the Catalog to JSON */
        $actual = $catalog->toJson();

        /** @Then the mapped JSON should have expected values */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $actual);
    }

    public function testObjectWithArrayIteratorNonTraversableProperty(): void
    {
        /** @Given an Inventory object with a non-traversable property */
        $inventory = new Inventory(stock: new ArrayIterator(['item-A', 'item-B', 'item-C']));

        /** @When mapping the Inventory to an array */
        $actual = $inventory->toArray();

        /** @Then the mapped array should have expected values */
        $expected = ['item-A', 'item-B', 'item-C'];

        self::assertSame($expected, $actual);

        /** @And when mapping the Inventory to JSON */
        $actual = $inventory->toJson();

        /** @Then the mapped JSON should have expected values */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $actual);
    }

    public function testObjectWithoutConstructorWithDefaultValues(): void
    {
        /** @Given a Webhook with no constructor using default property values */
        $webhook = new Webhook();

        /** @When mapping the Webhook to an array */
        $actual = $webhook->toArray();

        /** @Then the mapped array should have expected default values */
        $expected = [
            'url'    => '',
            'active' => false
        ];

        self::assertSame($expected, $actual);

        /** @And when mapping the Webhook to JSON */
        $actual = $webhook->toJson();

        /** @Then the mapped JSON should have expected values */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $actual);
    }

    public static function objectProvider(): array
    {
        return [
            'Tag object'          => [
                'class'    => Tag::class,
                'iterable' => [],
                'expected' => [
                    'name'  => '',
                    'color' => 'gray'
                ]
            ],
            'Member object'       => [
                'class'    => Member::class,
                'iterable' => [
                    'id'             => new MemberId(value: new Uuid(value: '88f15d3f-c9b9-4855-9778-5ba7926b6736')),
                    'role'           => 'owner',
                    'isOwner'        => true,
                    'organizationId' => new OrganizationId(
                        value: new Uuid(value: 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23')
                    )
                ],
                'expected' => [
                    'id'             => '88f15d3f-c9b9-4855-9778-5ba7926b6736',
                    'role'           => 'owner',
                    'isOwner'        => true,
                    'organizationId' => 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23'
                ],
            ],
            'Service object'      => [
                'class'    => Service::class,
                'iterable' => ['action' => static fn() => 'executed'],
                'expected' => ['action' => []]
            ],
            'Product object'      => [
                'class'    => Product::class,
                'iterable' => [
                    'id'          => 1,
                    'amount'      => Amount::from(value: 99.99, currency: Currency::USD),
                    'description' => new Description(text: 'A high-quality product'),
                    'attributes'  => new ArrayIterator([
                        'color'   => 'red',
                        'size'    => 'M',
                        'inStock' => true
                    ]),
                    'inventory'   => ['stock' => 100, 'warehouse' => 'A1'],
                    'status'      => ProductStatus::ACTIVE,
                    'createdAt'   => new DateTimeImmutable('2026-01-01T10:00:00+00:00')
                ],
                'expected' => [
                    'id'          => 1,
                    'amount'      => ['value' => 99.99, 'currency' => 'USD'],
                    'description' => 'A high-quality product',
                    'attributes'  => ['color' => 'red', 'size' => 'M', 'inStock' => true],
                    'inventory'   => ['stock' => 100, 'warehouse' => 'A1'],
                    'status'      => 1,
                    'createdAt'   => '2026-01-01T10:00:00+00:00'
                ],
            ],
            'Employee object'     => [
                'class'    => Employee::class,
                'iterable' => [
                    'name'   => 'John',
                    'active' => false
                ],
                'expected' => [
                    'name'       => 'John',
                    'department' => 'general',
                    'active'     => false
                ],
            ],
            'Organization object' => [
                'class'    => Organization::class,
                'iterable' => [
                    'id'          => new OrganizationId(
                        value: new Uuid(value: 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23')
                    ),
                    'name'        => 'Tech Corp',
                    'members'     => Members::createFrom(elements: [
                        new Member(
                            id: new MemberId(value: new Uuid(value: '88f15d3f-c9b9-4855-9778-5ba7926b6736')),
                            role: 'owner',
                            isOwner: true,
                            organizationId: new OrganizationId(
                                value: new Uuid(value: 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23')
                            )
                        ),
                        new Member(
                            id: new MemberId(value: new Uuid(value: 'c23b4c0a-f6d1-4b02-af2a-28b120a0ceb6')),
                            role: 'admin',
                            isOwner: false,
                            organizationId: new OrganizationId(
                                value: new Uuid(value: 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23')
                            )
                        )
                    ]),
                    'invitations' => []
                ],
                'expected' => [
                    'id'          => 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23',
                    'name'        => 'Tech Corp',
                    'members'     => [
                        [
                            'id'             => '88f15d3f-c9b9-4855-9778-5ba7926b6736',
                            'role'           => 'owner',
                            'isOwner'        => true,
                            'organizationId' => 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23'
                        ],
                        [
                            'id'             => 'c23b4c0a-f6d1-4b02-af2a-28b120a0ceb6',
                            'role'           => 'admin',
                            'isOwner'        => false,
                            'organizationId' => 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23'
                        ]
                    ],
                    'invitations' => []
                ]
            ]
        ];
    }

    public static function objectDiscardKeysProvider(): array
    {
        return [
            'Amount object with discard keys'   => [
                'object'   => Amount::fromIterable(iterable: ['value' => 100.50, 'currency' => 'USD']),
                'expected' => [100.50, 'USD']
            ],
            'Employee object with discard keys' => [
                'object'   => new Employee(
                    name: 'Gustavo',
                    department: 'Technology',
                    active: true
                ),
                'expected' => ['Gustavo', 'Technology', true]
            ]
        ];
    }
}
